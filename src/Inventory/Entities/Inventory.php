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

use Inventory\Entities\VO\InventoryStatus;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\Amount;

final readonly class Inventory
{
    /**
     * @param array<ResourceUuid> $zoneStorageIds
     */
    public static function create(ResourceUuid $uuid, array $zoneStorageIds, \DateTimeImmutable $date): self
    {
        return new self(
            uuid: $uuid,
            zoneStorageIds: $zoneStorageIds,
            date: $date,
            status: InventoryStatus::DRAFT,
            amount: Amount::zero(),
            items: new InventoryCollection(totalItems: 0)
        );
    }

    /**
     * @param array<ResourceUuid> $zoneStorageIds
     */
    private function __construct(
        private ResourceUuid $uuid,
        private array $zoneStorageIds,
        private \DateTimeImmutable $date,
        private InventoryStatus $status,
        private Amount $amount,
        private InventoryCollection $items
    ) {
    }

    public function uuid(): ResourceUuid
    {
        return $this->uuid;
    }

    /**
     * @return array<ResourceUuid>
     */
    public function zoneStorageIds(): array
    {
        return $this->zoneStorageIds;
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

    public function items(): InventoryCollection
    {
        return $this->items;
    }
}
