<?php

declare(strict_types=1);

/*
 * This file is part of the Tests package.
 *
 * (c) Dev-Int Création <info@developpement-interessant.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inventory\Adapters\Gateway\ORM\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Inventory\Adapters\Gateway\ORM\Entity\Inventory;
use Inventory\Adapters\Gateway\ORM\Entity\InventoryItem;
use Inventory\Adapters\Gateway\ORM\Entity\InventoryItemPackaging;
use Inventory\Adapters\Gateway\ORM\Entity\InventoryStatus as ORMInventoryStatus;
use Inventory\Adapters\Gateway\ORM\InventoryMapper;
use Inventory\Entities\Exception\InventoryNotFound;
use Inventory\Entities\Inventory as InventoryDomain;
use Inventory\Entities\InventoryCollection;
use Inventory\Entities\InventorySearchCriteria;
use Inventory\Entities\Repository\InventoryRepository;
use Shared\Adapters\Gateway\Filter\DateFilter;
use Shared\Adapters\Gateway\Filter\FilterCollection;
use Shared\Adapters\Gateway\Filter\JsonContainsFilter;
use Shared\Adapters\Gateway\Filter\SearchFilter;
use Shared\Entities\ResourceUuid;

/**
 * @template-extends ServiceEntityRepository<Inventory>
 */
final class DoctrineInventoryRepository extends ServiceEntityRepository implements InventoryRepository
{
    public const string ALIAS = 'inventory';

    public function __construct(ManagerRegistry $registry, private readonly InventoryMapper $mapper)
    {
        parent::__construct($registry, Inventory::class);
    }

    /**
     * @param array<ResourceUuid> $zoneStorageIds
     */
    public function hasActiveForZone(array $zoneStorageIds): bool
    {
        $zoneIds = '{' . implode(
            ',',
            array_map(static fn (ResourceUuid $id) => '"' . $id->toString() . '"', $zoneStorageIds)
        ) . '}';

        $sql = <<<'SQL'
            SELECT inventory.uuid
            FROM inventory
            WHERE inventory.status IN (:status)
            AND inventory.zone_storage_ids::jsonb ??| :zoneStorageIds::text[]
            SQL;
        $stmt = $this->getEntityManager()->getConnection()->executeQuery(
            $sql,
            [
                'status' => ORMInventoryStatus::ACTIVE_STATUSES,
                'zoneStorageIds' => $zoneIds,
            ],
            [
                'status' => ArrayParameterType::STRING,
                'zoneStorageIds' => ParameterType::STRING,
            ]
        );

        $inventories = $stmt->fetchAllAssociative();

        return $inventories !== [];
    }

    public function create(InventoryDomain $inventory): void
    {
        $inventoryOrm = $this->mapper->fromDomain($inventory);

        $this->getEntityManager()->persist($inventoryOrm);
        $this->getEntityManager()->flush();
    }

    public function start(InventoryDomain $inventory): void
    {
        $inventoryOrm = $this->find($inventory->uuid()->toString());

        if (!$inventoryOrm instanceof Inventory) {
            throw new InventoryNotFound($inventory->uuid());
        }

        $statusUpdatedAt = $inventory->statusUpdatedAt();
        \assert($statusUpdatedAt instanceof \DateTimeImmutable, 'statusUpdatedAt must be set when starting inventory');

        $inventoryOrm->start($statusUpdatedAt);

        foreach ($inventory->items()->toArray() as $item) {
            $components = $item->realStockComponents();
            $ormItem = new InventoryItem(
                id: null,
                inventory: $inventoryOrm,
                articleId: $item->article()->toString(),
                articleName: $item->articleName()->toString(),
                zoneStorageId: $item->zoneStorage()->toString(),
                price: $item->price()->toInt(),
                theoreticalStock: $item->theoreticalStock()->toMilliemes(),
                realStock: $item->realStock()->toMilliemes(),
                realStockParcel: $components->parcel->toMilliemes(),
                realStockSubPackage: $components->subPackage->toMilliemes(),
                realStockConsumerUnit: $components->consumerUnit->toMilliemes(),
                amount: $item->amount()->toInt(),
            );

            $packagingOrm = InventoryItemPackaging::fromDomain($item->packaging(), $ormItem);
            $ormItem->setPackaging($packagingOrm);

            $inventoryOrm->addItem($ormItem);
        }

        $this->getEntityManager()->flush();
    }

    public function findByCriteria(InventorySearchCriteria $criteria): InventoryCollection
    {
        $alias = self::ALIAS;
        $qb = $this->createQueryBuilder($alias);

        // Applique les filtres avec les classes réutilisables
        $filters = new FilterCollection();
        $filters
            ->add(new SearchFilter(), $alias, 'status', $criteria->status)
            ->add(DateFilter::after(), $alias, 'date', $criteria->dateAfter)
            ->add(DateFilter::before(), $alias, 'date', $criteria->dateBefore)
            ->add(new JsonContainsFilter(), $alias, 'zoneStorages', $criteria->zoneStorageUuid)
            ->apply($qb)
        ;

        // Tri déterministe (fix PR #213) - tri tertiaire sur UUID garantit ordre stable
        $qb->orderBy("{$alias}.date", 'DESC')
            ->addOrderBy("{$alias}.createdAt", 'DESC')
            ->addOrderBy("{$alias}.uuid", 'ASC')
        ;

        // Pagination
        $qb->setFirstResult(($criteria->page - 1) * $criteria->itemsPerPage)
            ->setMaxResults($criteria->itemsPerPage)
        ;

        $paginator = new Paginator($qb->getQuery(), fetchJoinCollection: true);
        $collection = new InventoryCollection($paginator->count());

        /** @var Inventory $inventory */
        foreach ($paginator as $inventory) {
            $collection->add($this->mapper->toDomain($inventory));
        }

        return $collection;
    }

    public function getByUuid(ResourceUuid $uuid): InventoryDomain
    {
        $inventory = $this->find($uuid->toString());

        if (!$inventory instanceof Inventory) {
            throw new InventoryNotFound($uuid);
        }

        return $this->mapper->toDomain($inventory);
    }

    public function save(InventoryDomain $inventory): void
    {
        $inventoryOrm = $this->find($inventory->uuid()->toString());

        if (!$inventoryOrm instanceof Inventory) {
            throw new InventoryNotFound($inventory->uuid());
        }

        $inventoryOrm->updateStatus(
            ORMInventoryStatus::fromDomain($inventory->status()),
            $inventory->statusUpdatedAt()
        );
        $inventoryOrm->updateDiscrepancyAmount($inventory->discrepancyAmount()->toInt());

        foreach ($inventory->items()->toArray() as $domainItem) {
            $ormItem = $inventoryOrm->findItemByArticleAndZone(
                $domainItem->article()->toString(),
                $domainItem->zoneStorage()->toString()
            );

            if ($ormItem instanceof InventoryItem) {
                $this->mapper->updateOrmItem($ormItem, $domainItem);
            }
        }

        $this->getEntityManager()->flush();
    }
}
