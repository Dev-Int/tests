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

use Admin\Entities\Exception\Unit\InvalidUnitException;
use Admin\Entities\Unit\Unit;

final class Storage
{
    public const UNITS = [
        'bouteille',
        'boîte',
        'carton',
        'colis',
        'kilogramme',
        'litre',
        'pièce',
        'poche',
        'portion',
    ];

    /**
     * @param array{Unit, float} $storage
     */
    public static function fromArray(array $storage): self
    {
        $unit = self::isValidUnit($storage[0]);
        $quantity = self::isValidQuantity($storage[1]);

        return new self($unit, $quantity);
    }

    private function __construct(private readonly Unit $unit, private readonly float $quantity)
    {
    }

    public function unit(): Unit
    {
        return $this->unit;
    }

    public function quantity(): float
    {
        return $this->quantity;
    }

    /**
     * @return array{Unit, float}
     */
    public function toArray(): array
    {
        return [$this->unit, $this->quantity];
    }

    private static function isValidUnit(Unit $unit): Unit
    {
        if (!\in_array(strtolower($unit->label()->toString()), self::UNITS, true)) {
            throw new InvalidUnitException($unit->label()->toString());
        }

        return $unit;
    }

    private static function isValidQuantity(float $quantity): float
    {
        return $quantity;
    }
}
