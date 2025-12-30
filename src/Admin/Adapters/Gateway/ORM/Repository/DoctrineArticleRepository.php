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

namespace Admin\Adapters\Gateway\ORM\Repository;

use Admin\Adapters\Gateway\ORM\Entity\Article\Article;
use Admin\Adapters\Gateway\ORM\Entity\Article\Packaging;
use Admin\Adapters\Gateway\ORM\Entity\FamilyLog\FamilyLog;
use Admin\Adapters\Gateway\ORM\Entity\Supplier;
use Admin\Adapters\Gateway\ORM\Entity\Tax;
use Admin\Adapters\Gateway\ORM\Entity\Unit;
use Admin\Adapters\Gateway\ORM\Entity\ZoneStorage;
use Admin\Entities\Article\Article as ArticleDomain;
use Admin\Entities\Article\ArticleCollection;
use Admin\Entities\Article\VO\Packaging as PackagingDomain;
use Admin\Entities\Event\LowStockDetected;
use Admin\Entities\Exception\Article\ArticleNotFound;
use Admin\Entities\Exception\Article\NoArticleRegistered;
use Admin\Entities\Exception\Article\PackagingNotFound;
use Admin\Entities\Exception\FamilyLog\FamilyLogNotFound;
use Admin\Entities\Exception\Supplier\SupplierNotFound;
use Admin\Entities\Exception\Tax\TaxNotFound;
use Admin\Entities\Exception\Unit\UnitNotFound;
use Admin\Entities\Exception\ZoneStorage\ZoneStorageNotFound;
use Admin\Entities\Repository\ArticleRepository;
use Admin\Entities\Unit\Unit as UnitDomain;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\UnexpectedResultException;
use Doctrine\Persistence\ManagerRegistry;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\Quantity;

/**
 * @template-extends ServiceEntityRepository<Article>
 */
final class DoctrineArticleRepository extends ServiceEntityRepository implements ArticleRepository
{
    public const ALIAS = 'article';

    public function __construct(
        ManagerRegistry $registry,
        private readonly DoctrinePackagingRepository $packagingRepository,
        private readonly DoctrineSupplierRepository $supplierRepository,
        private readonly DoctrineTaxRepository $taxRepository,
        private readonly DoctrineZoneStorageRepository $zoneStorageRepository,
        private readonly DoctrineFamilyLogRepository $familyLogRepository,
        private readonly DoctrineUnitRepository $unitRepository,
    ) {
        parent::__construct($registry, Article::class);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function isExists(string $name): bool
    {
        $alias = self::ALIAS;
        $article = $this->createQueryBuilder($alias)
            ->where("{$alias}.name = :name")
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $article !== null;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException|UnexpectedResultException
     */
    public function hasArticle(): bool
    {
        $alias = self::ALIAS;
        $count = $this->createQueryBuilder($alias)
            ->select('COUNT(1)')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        if (!\is_int($count)) {
            // @codeCoverageIgnoreStart
            throw new UnexpectedResultException('Integer expected!');
            // @codeCoverageIgnoreEnd
        }

        return $count > 0;
    }

    public function save(ArticleDomain $article): void
    {
        $supplier = $this->supplierRepository->find($article->supplier()->uuid()->toString());
        if (!$supplier instanceof Supplier) {
            // @codeCoverageIgnoreStart
            throw new SupplierNotFound($article->supplier()->uuid()->toString());
            // @codeCoverageIgnoreEnd
        }

        $tax = $this->taxRepository->find($article->tax()->uuid()->toString());
        if (!$tax instanceof Tax) {
            // @codeCoverageIgnoreStart
            throw new TaxNotFound($article->tax()->uuid()->toString());
            // @codeCoverageIgnoreEnd
        }

        $zoneStorages = new ArrayCollection();
        foreach ($article->zoneStorages()->toArray() as $zoneStorage) {
            $zoneStorageOrm = $this->zoneStorageRepository->find($zoneStorage->uuid()->toString());
            if (!$zoneStorageOrm instanceof ZoneStorage) {
                // @codeCoverageIgnoreStart
                throw new ZoneStorageNotFound($zoneStorage->slug());
                // @codeCoverageIgnoreEnd
            }
            $zoneStorages->add($zoneStorageOrm);
        }
        $familyLog = $this->familyLogRepository->find($article->familyLog()->uuid()->toString());

        if (!$familyLog instanceof FamilyLog) {
            // @codeCoverageIgnoreStart
            throw new FamilyLogNotFound($article->familyLog()->uuid()->toString());
            // @codeCoverageIgnoreEnd
        }

        $articleOrm = Article::fromDomain(
            $article,
            $supplier,
            $tax,
            $zoneStorages,
            $familyLog
        );
        $packaging = $this->getPackagingFromDomain($article->packaging(), $articleOrm);
        $articleOrm->setPackaging($packaging);

        $this->getEntityManager()->persist($articleOrm);
        $this->getEntityManager()->flush();
    }

    public function renameArticle(ArticleDomain $article): void
    {
        $articleToUpdate = $this->find($article->uuid()->toString());

        if (!$articleToUpdate instanceof Article) {
            // @codeCoverageIgnoreStart
            throw new ArticleNotFound($article->uuid()->toString());
            // @codeCoverageIgnoreEnd
        }

        $articleToUpdate->setName($article->name()->toString())
            ->setSlug($article->slug())
        ;

        $this->getEntityManager()->flush();
    }

    public function reAssignSupplier(ArticleDomain $article): void
    {
        $articleToUpdate = $this->find($article->uuid()->toString());
        if (!$articleToUpdate instanceof Article) {
            // @codeCoverageIgnoreStart
            throw new ArticleNotFound($article->name()->toString());
            // @codeCoverageIgnoreEnd
        }
        $supplierOrm = $this->supplierRepository->find($article->supplier()->uuid()->toString());
        if (!$supplierOrm instanceof Supplier) {
            // @codeCoverageIgnoreStart
            throw new SupplierNotFound($article->supplier()->slug());
            // @codeCoverageIgnoreEnd
        }
        $familyLogOrm = $this->familyLogRepository->find($article->familyLog()->uuid()->toString());
        if (!$familyLogOrm instanceof FamilyLog) {
            // @codeCoverageIgnoreStart
            throw new FamilyLogNotFound($article->familyLog()->slug());
            // @codeCoverageIgnoreEnd
        }
        $zoneStorages = new ArrayCollection();
        foreach ($article->zoneStorages()->toArray() as $zoneStorage) {
            $zoneStorageOrm = $this->zoneStorageRepository->find($zoneStorage->uuid()->toString());
            if (!$zoneStorageOrm instanceof ZoneStorage) {
                // @codeCoverageIgnoreStart
                throw new ZoneStorageNotFound($zoneStorage->slug());
                // @codeCoverageIgnoreEnd
            }
            $zoneStorages->add($zoneStorageOrm);
        }

        $articleToUpdate->setSupplier($supplierOrm)
            ->setFamilyLog($familyLogOrm)
            ->setZoneStorages($zoneStorages)
        ;

        $this->getEntityManager()->flush();
    }

    public function changeStorageInformation(ArticleDomain $article): void
    {
        $articleToUpdate = $this->find($article->uuid()->toString());
        if (!$articleToUpdate instanceof Article) {
            // @codeCoverageIgnoreStart
            throw new ArticleNotFound($article->name()->toString());
            // @codeCoverageIgnoreEnd
        }

        $articleToUpdate = $this->updateArticlePackaging($article->packaging(), $articleToUpdate);
        $articleToUpdate->setMinStock($article->minStock());

        $this->getEntityManager()->flush();
    }

    public function changeFinancialInformation(ArticleDomain $article): void
    {
        $articleToUpdate = $this->find($article->uuid()->toString());
        if (!$articleToUpdate instanceof Article) {
            // @codeCoverageIgnoreStart
            throw new ArticleNotFound($article->name()->toString());
            // @codeCoverageIgnoreEnd
        }

        $tax = $this->taxRepository->find($article->tax()->uuid()->toString());
        if (!$tax instanceof Tax) {
            // @codeCoverageIgnoreStart
            throw new TaxNotFound($article->tax()->uuid()->toString());
            // @codeCoverageIgnoreEnd
        }

        $articleToUpdate
            ->setUnitPrice($article->unitPrice()->toInt())
            ->setTax($tax)
        ;

        $this->getEntityManager()->flush();
    }

    public function getAllArticlesPaginated(int $page, int $itemPerPage): ArticleCollection
    {
        $alias = self::ALIAS;
        $query = $this->createQueryBuilder($alias)
            ->leftJoin("{$alias}.packaging", 'packaging')
            ->addSelect('packaging')
            ->setFirstResult(($page - 1) * $itemPerPage)
            ->setMaxResults($itemPerPage)
        ;

        $articles = new Paginator($query, fetchJoinCollection: true);
        if ($articles->count() === 0) {
            throw new NoArticleRegistered();
        }

        $collection = new ArticleCollection($articles->count());

        /** @var Article $article */
        foreach ($articles as $article) {
            $collection->add($article->toDomain());
        }

        return $collection;
    }

    public function getByUuid(ResourceUuid $uuid): ArticleDomain
    {
        $alias = self::ALIAS;
        $article = $this->createQueryBuilder($alias)
            ->leftJoin("{$alias}.packaging", 'packaging')
            ->addSelect('packaging')
            ->where("{$alias}.uuid = :uuid")
            ->setParameter('uuid', $uuid->toString())
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (!$article instanceof Article) {
            // @codeCoverageIgnoreStart
            throw new ArticleNotFound($uuid->toString());
            // @codeCoverageIgnoreEnd
        }

        return $article->toDomain();
    }

    public function getBySlug(string $slug): ArticleDomain
    {
        $alias = self::ALIAS;
        $article = $this->createQueryBuilder($alias)
            ->leftJoin("{$alias}.packaging", 'packaging')
            ->addSelect('packaging')
            ->where("{$alias}.slug = :slug")
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (!$article instanceof Article) {
            throw new ArticleNotFound($slug);
        }

        return $article->toDomain();
    }

    /**
     * @param array<array{uuid: ResourceUuid, quantity: Quantity}> $updates
     *
     * @return array<LowStockDetected>
     */
    public function resetQuantities(array $updates): array
    {
        $events = [];

        foreach ($updates as $update) {
            $articleDomain = $this->getByUuid($update['uuid']);
            $event = $articleDomain->resetQuantity($update['quantity']);

            if ($event instanceof LowStockDetected) {
                $events[] = $event;
            }

            $articleOrm = $this->find($update['uuid']->toString());
            if ($articleOrm instanceof Article) {
                $articleOrm->setQuantity($update['quantity']->toUnit());
            }
        }

        $this->getEntityManager()->flush();

        return $events;
    }

    /**
     * @throws PackagingNotFound
     */
    private function updateArticlePackaging(
        PackagingDomain $packagingDomain,
        Article $articleToUpdate,
    ): Article {
        $packaging = $this->getPackagingFromDomain($packagingDomain, $articleToUpdate);
        $packagingToUpdate = $this->packagingRepository->find($articleToUpdate->packaging()->id());
        if (!$packagingToUpdate instanceof Packaging) {
            // @codeCoverageIgnoreStart
            throw new PackagingNotFound($articleToUpdate->packaging()->id());
            // @codeCoverageIgnoreEnd
        }

        $packagingToUpdate
            ->setParcelUnit($packaging->parcelUnit())
            ->setParcelQuantity($packaging->parcelQuantity())
            ->setSubPackageUnit($packaging->subPackageUnit())
            ->setSubPackageQuantity($packaging->subPackageQuantity())
            ->setConsumeUnitUnit($packaging->consumeUnitUnit())
            ->setConsumeUnitQuantity($packaging->consumeUnitQuantity())
        ;
        $articleToUpdate->setPackaging($packagingToUpdate);

        return $articleToUpdate;
    }

    private function getPackagingFromDomain(PackagingDomain $packagingDomain, Article $article): Packaging
    {
        [$parcelUnit, $parcelQuantity] = $this->getUnitWithSlug($packagingDomain->parcel());
        if ($parcelUnit === null || $parcelQuantity === null) {
            // @codeCoverageIgnoreStart
            throw new \InvalidArgumentException('Packaging domain must have a parcel');
            // @codeCoverageIgnoreEnd
        }

        [$subPackageUnit, $subPackageQuantity] = $this->getUnitWithSlug($packagingDomain->subPackage());
        [$consumeUnitUnit, $consumeUnitQuantity] = $this->getUnitWithSlug($packagingDomain->consumerUnit());

        return new Packaging(
            $article,
            $parcelUnit,
            $parcelQuantity,
            $subPackageUnit,
            $subPackageQuantity,
            $consumeUnitUnit,
            $consumeUnitQuantity
        );
    }

    /**
     * @param array{UnitDomain, float}|null $package
     *
     * @return array{Unit|null, float|null}
     *
     * @throws UnitNotFound
     */
    private function getUnitWithSlug(?array $package): array
    {
        if ($package === null) {
            return [null, null];
        }

        $unit = $this->unitRepository->findOneBy(['slug' => $package[0]->slug()]);
        if (!$unit instanceof Unit) {
            // @codeCoverageIgnoreStart
            throw new UnitNotFound($package[0]->slug());
            // @codeCoverageIgnoreEnd
        }

        return [$unit, $package[1]];
    }
}
