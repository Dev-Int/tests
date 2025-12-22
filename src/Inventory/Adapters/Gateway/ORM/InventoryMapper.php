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
use Inventory\Adapters\Gateway\ORM\Entity\InventoryStatus;
use Inventory\Entities\Inventory as InventoryDomain;
use Inventory\Entities\InventoryItem as InventoryItemDomain;
use Inventory\Entities\InventoryItemCollection;
use Inventory\Entities\VO\InventoryDate;
use Inventory\Entities\VO\InventoryStatus as InventoryStatusDomain;
use Inventory\Entities\VO\ZoneStorage;
use Inventory\UseCases\Gateway\ZoneStorageGateway;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\Amount;
use Shared\Entities\VO\Quantity;

final readonly class InventoryMapper
{
    public function __construct(private ZoneStorageGateway $zoneStorageGateway)
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
            settledAt: $inventoryDomain->statusUpdatedAt(),
            items: []
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
            date: InventoryDate::fromDateTimeImmutable($inventory->date()),
            status: InventoryStatusDomain::from($inventory->status()->value),
            amount: Amount::fromCents($inventory->amount()),
            createdAt: $inventory->createdAt(),
            updatedAt: $inventory->updatedAt(),
            statusUpdatedAt: $inventory->settledAt(),
        );
        foreach ($inventory->items() as $item) {
            $itemDomain = new InventoryItemDomain(
                article: ResourceUuid::fromString($item->articleId()),
                price: Amount::fromCents($item->price()),
                theoreticalStock: Quantity::fromMilliemes($item->theoreticalStock()),
                realStock: Quantity::fromMilliemes($item->realStock()),
                amount: Amount::fromCents($item->amount()),
            );
            $inventoryDomain->addItem($itemDomain);
        }

        return $inventoryDomain;
    }

    private function getItemsFromDomain(InventoryItemCollection $items, Inventory &$inventory): void
    {
        foreach ($items->toArray() as $item) {
            $inventory->addItem(
                new InventoryItem(
                    id: null,
                    inventory: $inventory,
                    articleId: $item->article()->toString(),
                    price: $item->price()->toInt(),
                    theoreticalStock: $item->theoreticalStock()->toMilliemes(),
                    realStock: $item->realStock()->toMilliemes(),
                    amount: $item->amount()->toInt(),
                )
            );
        }
    }
}
