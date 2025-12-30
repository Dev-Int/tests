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

namespace Shared\Entities\VO;

/**
 * Contract for quantity-like value objects that can be used in calculations.
 *
 * Implementations may represent positive-only values (Quantity)
 * or signed values (StockDifference).
 */
interface Quantifiable
{
    /**
     * Returns the value in milliemes (1/1000 of a unit).
     *
     * May be negative for signed implementations like StockDifference.
     */
    public function toMilliemes(): int;

    /**
     * Returns the value in units (milliemes / 1000).
     *
     * May be negative for signed implementations like StockDifference.
     */
    public function toUnit(): float;
}
