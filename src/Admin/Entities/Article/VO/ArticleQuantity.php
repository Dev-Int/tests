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

namespace Admin\Entities\Article\VO;

use Admin\Entities\Exception\Article\NegativeValueException;

final readonly class ArticleQuantity
{
    public static function fromFloat(float $quantity): self
    {
        if ($quantity < 0.0) {
            throw new NegativeValueException($quantity);
        }

        return new self((int) (round($quantity, 3) * 1000));
    }

    private function __construct(private int $value)
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
