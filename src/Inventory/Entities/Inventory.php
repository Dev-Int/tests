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

use Inventory\Entities\Exception\InvalidStatusTransition;
use Inventory\Entities\VO\InventoryDate;
use Inventory\Entities\VO\InventoryStatus;
use Inventory\Entities\VO\ZoneStorage;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\Amount;

final class Inventory
{
    /**
     * @param array<ZoneStorage> $zoneStorages
     */
    public static function create(ResourceUuid $uuid, array $zoneStorages, InventoryDate $date): self
    {
        return new self(
            uuid: $uuid,
            zoneStorages: $zoneStorages,
            date: $date,
            status: InventoryStatus::DRAFT,
            amount: Amount::zero(),
            createdAt: ClockFactory::clock()->now(),
            updatedAt: ClockFactory::clock()->now(),
            statusUpdatedAt: null,
            items: new InventoryItemCollection(totalItems: 0),
        );
    }

    /**
     * @param array<ZoneStorage> $zoneStorages
     */
    public static function reconstitute(
        ResourceUuid $uuid,
        array $zoneStorages,
        InventoryDate $date,
        InventoryStatus $status,
        Amount $amount,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
        ?\DateTimeImmutable $statusUpdatedAt,
    ): self {
        return new self(
            uuid: $uuid,
            zoneStorages: $zoneStorages,
            date: $date,
            status: $status,
            amount: $amount,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            statusUpdatedAt: $statusUpdatedAt,
            items: new InventoryItemCollection(totalItems: 0)
        );
    }

    /**
     * @param array<ZoneStorage> $zoneStorages
     */
    private function __construct(
        private readonly ResourceUuid $uuid,
        private readonly array $zoneStorages,
        private readonly InventoryDate $date,
        private InventoryStatus $status,
        private readonly Amount $amount,
        private readonly \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
        private ?\DateTimeImmutable $statusUpdatedAt,
        private readonly InventoryItemCollection $items,
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

    public function date(): InventoryDate
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

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function statusUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->statusUpdatedAt;
    }

    public function items(): InventoryItemCollection
    {
        return $this->items;
    }

    public function addItem(InventoryItem $itemDomain): void
    {
        $this->items->add($itemDomain);
    }

    /**
     * Démarre le traitement de l'inventaire (DRAFT → IN_PROGRESS).
     */
    public function startProcessing(): void
    {
        if (InventoryStatus::DRAFT !== $this->status) {
            throw new InvalidStatusTransition(fromStatus: $this->status, toStatus: InventoryStatus::IN_PROGRESS);
        }
        $this->status = InventoryStatus::IN_PROGRESS;
        $this->statusUpdatedAt = ClockFactory::clock()->now();
    }

    /**
     * Soumet l'inventaire pour révision (IN_PROGRESS → REVIEW).
     */
    public function submitForReview(): void
    {
        if (InventoryStatus::IN_PROGRESS !== $this->status) {
            throw new InvalidStatusTransition(fromStatus: $this->status, toStatus: InventoryStatus::REVIEW);
        }
        $this->status = InventoryStatus::REVIEW;
        $this->statusUpdatedAt = ClockFactory::clock()->now();
    }

    /**
     * Renvoie l'inventaire en traitement pour corrections (REVIEW → IN_PROGRESS).
     */
    public function sendBackToProcessing(): void
    {
        if (InventoryStatus::REVIEW !== $this->status) {
            throw new InvalidStatusTransition(fromStatus: $this->status, toStatus: InventoryStatus::IN_PROGRESS);
        }
        $this->status = InventoryStatus::IN_PROGRESS;
        $this->statusUpdatedAt = ClockFactory::clock()->now();
    }

    /**
     * Finalise l'inventaire (REVIEW → COMPLETED).
     */
    public function complete(): void
    {
        if (InventoryStatus::REVIEW !== $this->status) {
            throw new InvalidStatusTransition(fromStatus: $this->status, toStatus: InventoryStatus::COMPLETED);
        }
        $this->status = InventoryStatus::COMPLETED;
        $this->statusUpdatedAt = ClockFactory::clock()->now();
        $this->updatedAt = ClockFactory::clock()->now();
    }
}
