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

/**
 * Represents the stored quantities for each packaging level.
 *
 * Unlike RealStockEntry (floats for user input), this uses Quantity
 * for domain storage with millièmes precision.
 */
final readonly class RealStockComponents
{
    public static function zero(): self
    {
        return new self(
            Quantity::fromUnit(0),
            Quantity::fromUnit(0),
            Quantity::fromUnit(0),
        );
    }

    public static function fromUnits(float $parcel, float $subPackage, float $consumerUnit): self
    {
        return new self(
            Quantity::fromUnit($parcel),
            Quantity::fromUnit($subPackage),
            Quantity::fromUnit($consumerUnit),
        );
    }

    public static function fromMilliemes(int $parcel, int $subPackage, int $consumerUnit): self
    {
        return new self(
            Quantity::fromMilliemes($parcel),
            Quantity::fromMilliemes($subPackage),
            Quantity::fromMilliemes($consumerUnit),
        );
    }

    public function __construct(
        public Quantity $parcel,
        public Quantity $subPackage,
        public Quantity $consumerUnit,
    ) {
    }
}
