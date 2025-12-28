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

final readonly class DiscrepancyItemResult
{
    public function __construct(
        public string $identifier,
        public string $articleName,
        public float $theoreticalStock,
        public float $realStock,
        public float $difference,
        public bool $isPositive,
        public bool $isNegative,
        public bool $isReviewed,
    ) {
    }
}
