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

final class Amount
{
    public static function fromInt(int $amount): self
    {
        return new self($amount);
    }

    public static function fromFloat(float $amount): self
    {
        return new self((int) ($amount * 100));
    }

    private function __construct(private readonly int $amount)
    {
    }

    public function toInt(): int
    {
        return $this->amount;
    }

    public function toFloat(): float
    {
        return $this->amount / 100;
    }
}
