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

namespace Inventory\Entities;

use Inventory\Entities\ReadModel\ZoneStorage;
use Inventory\Entities\VO\InventoryStatus;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\Amount;

final readonly class Inventory
{
    /**
     * @param array<ZoneStorage> $zoneStorages
     */
    public static function create(ResourceUuid $uuid, array $zoneStorages, \DateTimeImmutable $date): self
    {
        return new self(
            uuid: $uuid,
            zoneStorages: $zoneStorages,
            date: $date,
            status: InventoryStatus::DRAFT,
            amount: Amount::zero(),
            items: new InventoryItemCollection(totalItems: 0)
        );
    }

    /**
     * @param array<ZoneStorage> $zoneStorages
     */
    public static function reconstitute(
        ResourceUuid $uuid,
        array $zoneStorages,
        \DateTimeImmutable $date,
        InventoryStatus $status,
        Amount $amount
    ): self {
        return new self(
            uuid: $uuid,
            zoneStorages: $zoneStorages,
            date: $date,
            status: $status,
            amount: $amount,
            items: new InventoryItemCollection(totalItems: 0)
        );
    }

    /**
     * @param array<ZoneStorage> $zoneStorages
     */
    private function __construct(
        private ResourceUuid $uuid,
        private array $zoneStorages,
        private \DateTimeImmutable $date,
        private InventoryStatus $status,
        private Amount $amount,
        private InventoryItemCollection $items
    ) {
    }

    public function uuid(): ResourceUuid
    {
        return $this->uuid;
    }

    /**
     * @return array<ZoneStorage>
     */
    public function zoneStorages(): array
    {
        return $this->zoneStorages;
    }

    public function date(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function status(): InventoryStatus
    {
        return $this->status;
    }

    public function amount(): Amount
    {
        return $this->amount;
    }

    public function items(): InventoryItemCollection
    {
        return $this->items;
    }

    public function addItem(InventoryItem $itemDomain): void
    {
        $this->items->add($itemDomain);
    }
}
