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

use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\Amount;
use Shared\Entities\VO\Quantity;

final readonly class InventoryItem
{
    public function __construct(
        private ResourceUuid $article,
        private Amount $price,
        private Quantity $theoreticalStock,
        private Quantity $realStock,
        private Amount $amount,
    ) {
    }

    public function article(): ResourceUuid
    {
        return $this->article;
    }

    public function price(): Amount
    {
        return $this->price;
    }

    public function theoreticalStock(): Quantity
    {
        return $this->theoreticalStock;
    }

    public function realStock(): Quantity
    {
        return $this->realStock;
    }

    public function amount(): Amount
    {
        return $this->amount;
    }
}
