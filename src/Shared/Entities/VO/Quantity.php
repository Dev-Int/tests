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

use Shared\Entities\Exception\NegativeQuantity;

/**
 * bcmath is used to avoid rounding errors.
 */
final readonly class Quantity
{
    /**
     * The smallest part of units is milliemes. Only 3 digits after the dot.
     */
    public static function fromUnit(float $quantity): self
    {
        if ($quantity < 0.0) {
            throw new NegativeQuantity($quantity);
        }

        return new self(bcmul((string) $quantity, '1000', 0));
    }

    public static function fromMilliemes(int $quantity): self
    {
        if ($quantity < 0) {
            throw new NegativeQuantity($quantity);
        }

        return new self((string) $quantity);
    }

    private function __construct(private string $value)
    {
    }

    /**
     * The smallest part of units is milliemes. Integer is better than float for calculations or storage.
     */
    public function toMilliemes(): int
    {
        return (int) $this->value;
    }

    /**
     * The smallest part of units is milliemes. Only 3 digits after the dot.
     */
    public function toUnit(): float
    {
        return (float) bcdiv($this->value, '1000', 3);
    }

    public function isEqual(self $anotherQuantity): bool
    {
        return $this->toMilliemes() === $anotherQuantity->toMilliemes();
    }
}
