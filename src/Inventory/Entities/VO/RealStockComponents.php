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

    public static function fromUnits(float $consumerUnit, float $subPackage, float $parcel): self
    {
        return new self(
            Quantity::fromUnit($consumerUnit),
            Quantity::fromUnit($subPackage),
            Quantity::fromUnit($parcel),
        );
    }

    public static function fromMilliemes(int $consumerUnit, int $subPackage, int $parcel): self
    {
        return new self(
            Quantity::fromMilliemes($consumerUnit),
            Quantity::fromMilliemes($subPackage),
            Quantity::fromMilliemes($parcel),
        );
    }

    public function __construct(
        public Quantity $consumerUnit,
        public Quantity $subPackage,
        public Quantity $parcel,
    ) {
    }
}
