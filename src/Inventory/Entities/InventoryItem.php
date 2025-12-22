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

use Inventory\Entities\VO\Article;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\Amount;
use Shared\Entities\VO\Quantity;

final readonly class InventoryItem
{
    public static function createFromArticle(Article $article): self
    {
        $realStock = Quantity::fromMilliemes(0);
        // amount = price (cents) × quantity (units) = cents
        $amountCents = (int) bcmul(
            (string) $article->unitPrice->toInt(),
            bcdiv((string) $article->quantity->toMilliemes(), '1000', 3),
            0
        );

        return new self(
            article: $article->uuid,
            price: $article->unitPrice,
            theoreticalStock: $article->quantity,
            realStock: $realStock,
            amount: Amount::fromCents($amountCents),
        );
    }

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
