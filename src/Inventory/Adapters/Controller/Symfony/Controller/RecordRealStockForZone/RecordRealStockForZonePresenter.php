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

namespace Inventory\Adapters\Controller\Symfony\Controller\RecordRealStockForZone;

use Inventory\Entities\InventoryItem;

final readonly class RecordRealStockForZonePresenter
{
    /**
     * @param array<InventoryItem> $items
     */
    public function __construct(
        private array $items,
    ) {
    }

    /**
     * @return array<InventoryItemResult>
     */
    public function present(): array
    {
        $results = [];
        foreach ($this->items as $item) {
            $results[] = new InventoryItemResult(
                articleUuid: $item->article()->toString(),
                articleName: $item->articleName()->toString(),
                articleSlug: $item->articleName()->slugify(),
                theoreticalStock: $item->theoreticalStock()->toUnit(),
                realStock: $item->realStock()->toUnit(),
            );
        }

        return $results;
    }
}
