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

namespace Inventory\UseCases\ReviewDiscrepancies;

use Inventory\Entities\Inventory;
use Inventory\Entities\InventoryItem;

final readonly class ReviewDiscrepanciesResponse
{
    /**
     * @param array<InventoryItem> $reviewedItems
     */
    public function __construct(
        public Inventory $inventory,
        public array $reviewedItems,
        public int $reviewedCount,
    ) {
    }
}
