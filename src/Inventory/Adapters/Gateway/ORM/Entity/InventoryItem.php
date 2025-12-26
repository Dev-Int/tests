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

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'inventory_item')]
#[ORM\Index(name: 'idx_inventory_item_inventory_id', columns: ['inventory_id'])]
#[ORM\Index(name: 'idx_inventory_item_article_zone', columns: ['article_id', 'zone_storage_id'])]
class InventoryItem
{
    public function __construct(
        #[ORM\Id]
        #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
        #[ORM\SequenceGenerator(sequenceName: 'inventory_item_id_seq')]
        #[ORM\Column(name: 'id', type: 'integer', unique: true)]
        private ?int $id,
        #[ORM\ManyToOne(targetEntity: Inventory::class, inversedBy: 'items')]
        #[ORM\JoinColumn(name: 'inventory_id', referencedColumnName: 'uuid', nullable: false)]
        private Inventory $inventory,
        #[ORM\Column(name: 'article_id', type: 'guid', nullable: false)]
        private string $articleId,
        #[ORM\Column(name: 'article_name', type: 'string', length: 255, nullable: false)]
        private string $articleName,
        #[ORM\Column(name: 'zone_storage_id', type: 'guid', nullable: false)]
        private string $zoneStorageId,
        #[ORM\Column(name: 'price', type: 'integer')]
        private int $price,
        #[ORM\Column(name: 'theoretical_stock', type: 'integer')]
        private int $theoreticalStock,
        #[ORM\Column(name: 'real_stock', type: 'integer')]
        private int $realStock,
        #[ORM\Column(name: 'amount', type: 'integer')]
        private int $amount,
    ) {
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function inventory(): Inventory
    {
        return $this->inventory;
    }

    public function articleId(): string
    {
        return $this->articleId;
    }

    public function articleName(): string
    {
        return $this->articleName;
    }

    public function zoneStorageId(): string
    {
        return $this->zoneStorageId;
    }

    public function price(): int
    {
        return $this->price;
    }

    public function theoreticalStock(): int
    {
        return $this->theoreticalStock;
    }

    public function realStock(): int
    {
        return $this->realStock;
    }

    public function amount(): int
    {
        return $this->amount;
    }

    public function updateRealStock(int $realStock): void
    {
        $this->realStock = $realStock;
    }
}
