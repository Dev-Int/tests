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

final readonly class Amount
{
    public static function fromCents(int $amount): self
    {
        return new self((string) $amount);
    }

    public static function fromFloat(float $amount): self
    {
        $amountInCents = $amount * 100;

        return new self((string) $amountInCents);
    }

    public static function zero(): self
    {
        return new self('0');
    }

    private function __construct(private string $amount)
    {
    }

    public function toInt(): int
    {
        return (int) $this->amount;
    }

    public function toFloat(): float
    {
        return (float) bcdiv($this->amount, '100', 2);
    }

    /**
     * Computes amount × quantity.
     *
     * Accepts any Quantifiable (Quantity, StockDifference, etc.).
     * The result sign follows the quantity sign (negative for shortages).
     */
    public function computeQuantity(Quantifiable $quantity): self
    {
        $result = bcmul($this->amount, (string) $quantity->toUnit(), 0);

        return new self($result);
    }

    /**
     * Adds another amount to this one.
     *
     * Supports negative amounts (e.g., losses from stock shortages).
     */
    public function add(self $other): self
    {
        return new self(bcadd($this->amount, $other->amount, 0));
    }
}
