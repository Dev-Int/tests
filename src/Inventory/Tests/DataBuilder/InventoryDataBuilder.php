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

namespace Inventory\Tests\DataBuilder;

use Inventory\Entities\Inventory;
use Inventory\Entities\VO\InventoryDate;
use Inventory\Entities\VO\InventoryStatus;
use Inventory\Entities\VO\ZoneStorage;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\Amount;

final class InventoryDataBuilder
{
    private int $amount = 0;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;
    private ?\DateTimeImmutable $settledAt;

    /**
     * @param array<ZoneStorage> $zoneStorages
     */
    public static function create(
        ResourceUuid $uuid,
        InventoryDate $date,
        array $zoneStorages,
        InventoryStatus $status
    ): self {
        return new self($uuid, $date, $zoneStorages, $status);
    }

    /**
     * @param array<ZoneStorage> $zoneStorages
     */
    public function __construct(
        private ResourceUuid $uuid,
        private InventoryDate $date,
        private array $zoneStorages,
        private InventoryStatus $status
    ) {
        $now = ClockFactory::clock()->now();
        $this->createdAt = $now;
        $this->updatedAt = $now;
        $this->settledAt = $status === InventoryStatus::DRAFT ? null : $now;
    }

    public function withUuid(ResourceUuid $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @param array<ZoneStorage> $zoneStorages
     */
    public function withZoneStorages(array $zoneStorages): self
    {
        $this->zoneStorages = $zoneStorages;

        return $this;
    }

    public function withDate(InventoryDate $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function withStatus(InventoryStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function withAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function withCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function withUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function withSettledAt(?\DateTimeImmutable $settledAt): self
    {
        $this->settledAt = $settledAt;

        return $this;
    }

    public function build(): Inventory
    {
        return Inventory::reconstitute(
            uuid: $this->uuid,
            zoneStorages: $this->zoneStorages,
            date: $this->date,
            status: $this->status,
            amount: Amount::fromCents($this->amount),
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
            statusUpdatedAt: $this->settledAt
        );
    }
}
