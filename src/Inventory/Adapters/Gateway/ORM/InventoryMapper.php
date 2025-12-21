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

use Admin\Contracts\Services\Provider\ZoneStorage\ZoneStorageProvider;
use Inventory\Adapters\Gateway\ORM\Entity\Inventory;
use Inventory\Adapters\Gateway\ORM\Entity\InventoryItem;
use Inventory\Adapters\Gateway\ORM\Entity\InventoryStatus;
use Inventory\Entities\Inventory as InventoryDomain;
use Inventory\Entities\InventoryItem as InventoryItemDomain;
use Inventory\Entities\InventoryItemCollection;
use Inventory\Entities\ReadModel\ZoneStorage;
use Inventory\Entities\VO\InventoryStatus as InventoryStatusDomain;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\Amount;

final readonly class InventoryMapper
{
    public function __construct(private ZoneStorageProvider $zoneStorageProvider)
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
            date: $inventoryDomain->date(),
            zoneStorages: $zoneStorageIds,
            status: InventoryStatus::fromDomain($inventoryDomain->status()),
            amount: $inventoryDomain->amount()->toInt(),
            createdAt: $inventoryDomain->createdAt(),
            updatedAt: $inventoryDomain->updatedAt(),
            settledAt: $inventoryDomain->settledAt(),
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
            $zone = $this->zoneStorageProvider->provide($zoneUuid);
            $zoneStorages[] = new ZoneStorage(uuid: $zoneUuid, label: $zone->label);
        }
        $inventoryDomain = InventoryDomain::reconstitute(
            uuid: ResourceUuid::fromString($inventory->uuid()),
            zoneStorages: $zoneStorages,
            date: $inventory->date(),
            status: InventoryStatusDomain::from($inventory->status()->value),
            createdAt: $inventory->createdAt(),
            updatedAt: $inventory->updatedAt(),
            settledAt: $inventory->settledAt(),
            amount: Amount::fromCents($inventory->amount()),
        );
        foreach ($inventory->items() as $item) {
            $itemDomain = new InventoryItemDomain(
                article: ResourceUuid::fromString($item->articleId()),
                price: Amount::fromCents($item->price()),
                theoreticalStock: $item->theoreticalStock(),
                realStock: $item->realStock(),
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
                    theoreticalStock: $item->theoreticalStock(),
                    realStock: $item->realStock(),
                    amount: $item->amount()->toInt(),
                )
            );
        }
    }
}
