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

namespace Inventory\Adapters\Gateway\ORM\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Inventory\Adapters\Gateway\ORM\Repository\DoctrineInventoryRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: DoctrineInventoryRepository::class)]
#[ORM\Table(name: 'inventory')]
#[UniqueEntity(fields: ['date', 'zoneStorages'])]
#[ORM\Index(name: 'idx_inventory_status', columns: ['status'])]
#[ORM\Index(name: 'idx_inventory_date', columns: ['date'])]
class Inventory
{
    /**
     * @param array<string>                                             $zoneStorages
     * @param array<InventoryItem>|Collection<array-key, InventoryItem> $items
     */
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(name: 'uuid', type: 'guid')]
        private readonly string $uuid,
        #[ORM\Column(name: 'date', type: 'datetimetz_immutable')]
        private readonly \DateTimeImmutable $date,
        #[ORM\Column(name: 'zone_storage_ids', type: 'json')]
        private array $zoneStorages,
        #[ORM\Column(name: 'status', type: 'string', enumType: InventoryStatus::class)]
        private InventoryStatus $status,
        #[ORM\Column(name: 'amount', type: 'integer')]
        private int $amount,
        #[ORM\Column(name: 'created_at', type: 'datetimetz_immutable')]
        private readonly \DateTimeImmutable $createdAt,
        #[ORM\Column(name: 'updated_at', type: 'datetimetz_immutable')]
        private readonly \DateTimeImmutable $updatedAt,
        #[ORM\OneToMany(targetEntity: InventoryItem::class, mappedBy: 'inventory', cascade: ['persist', 'remove'])]
        private array|Collection $items,
        #[ORM\Column(name: 'status_updated_at', type: 'datetimetz_immutable', nullable: true)]
        private ?\DateTimeImmutable $statusUpdatedAt = null,
    ) {
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function date(): \DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @return array<string>
     */
    public function zoneStorages(): array
    {
        return $this->zoneStorages;
    }

    public function status(): InventoryStatus
    {
        return $this->status;
    }

    public function amount(): int
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

    /**
     * @return array<InventoryItem>|Collection<array-key, InventoryItem>
     */
    public function items(): array|Collection
    {
        return $this->items;
    }

    public function addItem(InventoryItem $item): void
    {
        $this->items[] = $item;
    }

    public function start(\DateTimeImmutable $statusUpdatedAt): void
    {
        $this->status = InventoryStatus::IN_PROGRESS;
        $this->statusUpdatedAt = $statusUpdatedAt;
    }
}
