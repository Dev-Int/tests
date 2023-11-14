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

use Article\Entities\Exception\InvalidUnitException;

final class Storage
{
    public const UNITS = [
        'bouteille',
        'boite',
        'carton',
        'colis',
        'kilogramme',
        'litre',
        'pièce',
        'poche',
        'portion',
    ];

    private string $unit;
    private float $quantity;

    /**
     * @param array{string, float} $storage
     */
    public static function fromArray(array $storage): self
    {
        $unit = self::isValidUnit($storage[0]);
        $quantity = self::isValidQuantity($storage[1]);

        return new self($unit, $quantity);
    }

    public function __construct(string $unit, float $quantity)
    {
        $this->unit = $unit;
        $this->quantity = $quantity;
    }

    /**
     * @return array{string, float}
     */
    public function toArray(): array
    {
        return [$this->unit, $this->quantity];
    }

    private static function isValidUnit(string $unit): string
    {
        if (!\in_array(strtolower($unit), self::UNITS, true)) {
            throw new InvalidUnitException($unit);
        }

        return strtolower($unit);
    }

    private static function isValidQuantity(float $quantity): float
    {
        return $quantity;
    }
}
