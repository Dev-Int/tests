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
final readonly class InventoryItem
{
    public function __construct(
        #[ORM\Id]
        #[ORM\GeneratedValue]
        #[ORM\Column(name: 'id', type: 'integer', unique: true)]
        private ?int $id,
        #[ORM\ManyToOne(targetEntity: Inventory::class, inversedBy: 'items')]
        #[ORM\JoinColumn(name: 'inventory_id', referencedColumnName: 'uuid', nullable: false)]
        private Inventory $inventory,
        #[ORM\Column(name: 'article_id', type: 'guid', nullable: false)]
        private string $articleId,
        #[ORM\Column(name: 'price', type: 'integer')]
        private int $price,
        #[ORM\Column(name: 'theoretical_stock', type: 'float')]
        private float $theoreticalStock,
        #[ORM\Column(name: 'real_stock', type: 'float')]
        private float $realStock,
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

    public function price(): int
    {
        return $this->price;
    }

    public function theoreticalStock(): float
    {
        return $this->theoreticalStock;
    }

    public function realStock(): float
    {
        return $this->realStock;
    }

    public function amount(): int
    {
        return $this->amount;
    }
}
