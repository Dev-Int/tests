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

final readonly class InventoryItemResult
{
    public function __construct(
        public string $articleUuid,
        public string $articleName,
        public string $articleSlug,
        public float $theoreticalStock,
        public float $realStock,
    ) {
    }
}
