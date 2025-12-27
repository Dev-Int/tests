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

namespace Inventory\Adapters\Controller\Symfony\Controller\ReviewInventory;

use Inventory\Entities\InventoryItem;

final readonly class ReviewInventoryPresenter
{
    /**
     * @param array<InventoryItem> $items
     */
    public function __construct(
        private array $items,
    ) {
    }

    /**
     * @return array<DiscrepancyItemResult>
     */
    public function present(): array
    {
        $results = [];

        foreach ($this->items as $item) {
            $difference = $item->calculateDifference();

            $results[] = new DiscrepancyItemResult(
                articleName: $item->articleName()->toString(),
                theoreticalStock: $item->theoreticalStock()->toUnit(),
                realStock: $item->realStock()->toUnit(),
                difference: $difference->toUnit(),
                isPositive: $difference->isPositive(),
                isNegative: $difference->isNegative(),
            );
        }

        return $results;
    }
}
