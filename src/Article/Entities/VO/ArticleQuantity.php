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

        return new self($quantity ?? 0.000);
    }

    private function __construct(private readonly float $value)
    {
    }

    public function getValue(): float
    {
        return round($this->value, 3);
    }
}
