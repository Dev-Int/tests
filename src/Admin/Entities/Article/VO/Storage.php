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

use Admin\Entities\Exception\Unit\InvalidUnit;
use Admin\Entities\Unit\Unit;

final class Storage
{
    public const string UNIT_BOUTEILLE = 'bouteille';
    public const string UNIT_BOITE = 'boîte';
    public const string UNIT_CARTON = 'carton';
    public const string UNIT_COLIS = 'colis';
    public const string UNIT_KILOGRAMME = 'kilogramme';
    public const string UNIT_LITRE = 'litre';
    public const string UNIT_PIECE = 'pièce';
    public const string UNIT_POCHE = 'poche';
    public const string UNIT_PORTION = 'portion';

    public const array UNITS = [
        self::UNIT_BOUTEILLE,
        self::UNIT_BOITE,
        self::UNIT_CARTON,
        self::UNIT_COLIS,
        self::UNIT_KILOGRAMME,
        self::UNIT_LITRE,
        self::UNIT_PIECE,
        self::UNIT_POCHE,
        self::UNIT_PORTION,
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
            throw new InvalidUnit($unit->label()->toString());
        }

        return $unit;
    }

    private static function isValidQuantity(float $quantity): float
    {
        return $quantity;
    }
}
