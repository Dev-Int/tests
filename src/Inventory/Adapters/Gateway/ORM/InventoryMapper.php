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

namespace Inventory\Adapters\Gateway\ORM;

use Inventory\Adapters\Gateway\ORM\Entity\Inventory;
use Inventory\Adapters\Gateway\ORM\Entity\InventoryItem;
use Inventory\Adapters\Gateway\ORM\Entity\InventoryItemPackaging;
use Inventory\Adapters\Gateway\ORM\Entity\InventoryStatus;
use Inventory\Entities\Inventory as InventoryDomain;
use Inventory\Entities\InventoryItem as InventoryItemDomain;
use Inventory\Entities\InventoryItemCollection;
use Inventory\Entities\VO\InventoryDate;
use Inventory\Entities\VO\InventoryStatus as InventoryStatusDomain;
use Inventory\Entities\VO\RealStockComponents;
use Inventory\Entities\VO\ZoneStorage;
use Inventory\UseCases\Gateway\ZoneStorageGatewayInterface;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\Amount;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\Quantity;

final readonly class InventoryMapper
{
    public function __construct(private ZoneStorageGatewayInterface $zoneStorageGateway)
    {
    }

    public function fromDomain(InventoryDomain $inventoryDomain): Inventory
    {
        $zoneStorageIds = array_map(
            static fn (ZoneStorage $zoneStorage) => $zoneStorage->uuid->toString(),
            $inventoryDomain->zoneStorages()
        );
        $inventory = new Inventory(
            uuid: $inventoryDomain->uuid()->toString(),
            date: $inventoryDomain->date()->toDateTimeImmutable(),
            zoneStorages: $zoneStorageIds,
            status: InventoryStatus::fromDomain($inventoryDomain->status()),
            amount: $inventoryDomain->amount()->toInt(),
            createdAt: $inventoryDomain->createdAt(),
            updatedAt: $inventoryDomain->updatedAt(),
            items: [],
            statusUpdatedAt: $inventoryDomain->statusUpdatedAt(),
            discrepancyAmount: $inventoryDomain->discrepancyAmount()->toInt(),
        );
        $this->getItemsFromDomain($inventoryDomain->items(), $inventory);

        return $inventory;
    }

    public function toDomain(Inventory $inventory): InventoryDomain
    {
        $zoneStorages = [];
        foreach ($inventory->zoneStorages() as $zoneStorageId) {
            $zoneUuid = ResourceUuid::fromString($zoneStorageId);
            $zone = $this->zoneStorageGateway->provide($zoneUuid);
            $zoneStorages[] = new ZoneStorage(uuid: $zoneUuid, name: $zone->name);
        }
        $inventoryDomain = InventoryDomain::reconstitute(
            uuid: ResourceUuid::fromString($inventory->uuid()),
            zoneStorages: $zoneStorages,
            date: InventoryDate::reconstitute($inventory->date()),
            status: InventoryStatusDomain::from($inventory->status()->value),
            amount: Amount::fromCents($inventory->amount()),
            discrepancyAmount: Amount::fromCents($inventory->discrepancyAmount()),
            createdAt: $inventory->createdAt(),
            updatedAt: $inventory->updatedAt(),
            statusUpdatedAt: $inventory->statusUpdatedAt(),
        );
        foreach ($inventory->items() as $item) {
            $itemDomain = new InventoryItemDomain(
                article: ResourceUuid::fromString($item->articleId()),
                articleName: NameField::fromString($item->articleName()),
                zoneStorage: ResourceUuid::fromString($item->zoneStorageId()),
                price: Amount::fromCents($item->price()),
                theoreticalStock: Quantity::fromMilliemes($item->theoreticalStock()),
                realStock: Quantity::fromMilliemes($item->realStock()),
                realStockComponents: RealStockComponents::fromMilliemes(
                    $item->realStockParcel() ?? 0,
                    $item->realStockSubPackage() ?? 0,
                    $item->realStockConsumerUnit() ?? 0,
                ),
                amount: Amount::fromCents($item->amount()),
                packaging: $item->packaging()->toDomain(),
                countedAt: $item->countedAt(),
                reviewed: $item->reviewed(),
            );
            $inventoryDomain->addItem($itemDomain);
        }

        return $inventoryDomain;
    }

    /**
     * Update an ORM item from a Domain item.
     * This method handles the mapping of updated values from Domain to ORM layer.
     */
    public function updateOrmItem(InventoryItem $ormItem, InventoryItemDomain $domainItem): void
    {
        $components = $domainItem->realStockComponents();
        $ormItem->updateRealStock(
            $domainItem->realStock()->toMilliemes(),
            $components->parcel->toMilliemes(),
            $components->subPackage->toMilliemes(),
            $components->consumerUnit->toMilliemes(),
            $domainItem->countedAt(),
        );
        $ormItem->updateReviewed($domainItem->isReviewed());
    }

    private function getItemsFromDomain(InventoryItemCollection $items, Inventory &$inventory): void
    {
        foreach ($items->toArray() as $item) {
            $components = $item->realStockComponents();
            $ormItem = new InventoryItem(
                id: null,
                inventory: $inventory,
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
                countedAt: $item->countedAt(),
            );

            $packagingOrm = InventoryItemPackaging::fromDomain($item->packaging(), $ormItem);
            $ormItem->setPackaging($packagingOrm);

            $inventory->addItem($ormItem);
        }
    }
}
