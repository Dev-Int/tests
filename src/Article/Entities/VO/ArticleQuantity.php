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

namespace Article\Entities\VO;

use Article\Entities\Exception\NegativeValueException;

final class ArticleQuantity
{
    public static function fromFloat(?float $quantity): self
    {
        if (null !== $quantity && $quantity < 0.0) {
            throw new NegativeValueException($quantity);
        }
        if ($quantity === null) {
            $quantity = 0.0;
        }

        return new self((int) (round($quantity, 3) * 1000));
    }

    private function __construct(private readonly int $value)
    {
    }

    public function toInt(): int
    {
        return $this->value;
    }

    public function toFloat(): float
    {
        return round($this->value / 1000, 3);
    }
}
