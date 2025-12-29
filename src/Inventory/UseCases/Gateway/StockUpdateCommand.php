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

namespace Inventory\UseCases\Gateway;

use Shared\Entities\ResourceUuid;

/**
 * Command to update an article's stock quantity.
 *
 * Used by the Inventory BC to communicate stock updates to the Admin BC
 * through the ArticleStockUpdaterInterface gateway.
 */
final readonly class StockUpdateCommand
{
    public function __construct(
        public ResourceUuid $articleUuid,
        public int $newQuantityMilliemes,
    ) {
    }
}
