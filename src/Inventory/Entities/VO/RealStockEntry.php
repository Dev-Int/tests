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

namespace Inventory\Entities\VO;

/**
 * Represents the quantities entered by user for each packaging level.
 *
 * Used with PackagingSnapshot::calculateTotalFromEntry() to compute
 * the total quantity in base units.
 */
final readonly class RealStockEntry
{
    public function __construct(
        public float $parcelQuantity = 0.0,
        public float $subPackageQuantity = 0.0,
        public float $consumerUnitQuantity = 0.0,
    ) {
    }
}
