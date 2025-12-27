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

use Shared\Entities\VO\Quantity;

final readonly class StockDifference
{
    public static function calculate(Quantity $realStock, Quantity $theoreticalStock): self
    {
        $differenceInMilliemes = $realStock->toMilliemes() - $theoreticalStock->toMilliemes();

        return new self($differenceInMilliemes);
    }

    private function __construct(private int $valueInMilliemes)
    {
    }

    public function toMilliemes(): int
    {
        return $this->valueInMilliemes;
    }

    public function toUnit(): float
    {
        return $this->valueInMilliemes / 1000.0;
    }

    public function isPositive(): bool
    {
        return $this->valueInMilliemes > 0;
    }

    public function isNegative(): bool
    {
        return $this->valueInMilliemes < 0;
    }

    public function isZero(): bool
    {
        return 0 === $this->valueInMilliemes;
    }
}
