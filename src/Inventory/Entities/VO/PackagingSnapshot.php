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
 * Snapshot of an article's packaging structure at inventory creation time.
 *
 * This preserves the packaging hierarchy for historical records,
 * even if the article's packaging changes after the inventory.
 *
 * Hierarchy: parcel (required) → subPackage (optional) → consumerUnit (optional)
 *
 * Example: 1 colis = 4 poches = 32 portions
 * - parcel: {label: "colis", abbr: "cls", qty: 1}
 * - subPackage: {label: "poche", abbr: "pch", qty: 4}
 * - consumerUnit: {label: "portion", abbr: "prt", qty: 8}
 */
final readonly class PackagingSnapshot
{
    public function __construct(
        public PackagingLevel $parcel,
        public ?PackagingLevel $subPackage = null,
        public ?PackagingLevel $consumerUnit = null,
    ) {
    }

    /**
     * @return list<PackagingLevel>
     */
    public function levels(): array
    {
        return array_values(array_filter([
            $this->parcel,
            $this->subPackage,
            $this->consumerUnit,
        ]));
    }

    public function hasMultipleLevels(): bool
    {
        return $this->subPackage instanceof PackagingLevel || $this->consumerUnit instanceof PackagingLevel;
    }

    /**
     * Calculates total quantity in base units from multi-level components.
     *
     * For packaging: 1 colis = 4 poches = 32 portions (8 portions/poche)
     * Components: 2 colis + 3 poches + 5 portions
     *
     * Calculation:
     * - 2 colis × 4 poches/colis × 8 portions/poche = 64 portions
     * - 3 poches × 8 portions/poche = 24 portions
     * - 5 portions = 5 portions
     * - Total = 64 + 24 + 5 = 93 portions
     */
    public function calculateTotalFromComponents(RealStockComponents $components): Quantity
    {
        $total = 0.0;

        // Parcel contribution: parcelQty × subPackage.qty × consumerUnit.qty
        $parcelMultiplier = $this->getParcelToBaseMultiplier();
        $total += $components->parcel->toUnit() * $parcelMultiplier;

        // SubPackage contribution: subPackageQty × consumerUnit.qty
        if ($this->subPackage instanceof PackagingLevel) {
            $subPackageMultiplier = $this->getSubPackageToBaseMultiplier();
            $total += $components->subPackage->toUnit() * $subPackageMultiplier;
        }

        // ConsumerUnit contribution: direct base units
        if ($this->consumerUnit instanceof PackagingLevel) {
            $total += $components->consumerUnit->toUnit();
        }

        return Quantity::fromUnit($total);
    }

    /**
     * Returns the multiplier to convert parcel quantity to base units.
     *
     * If packaging is: 1 colis = 4 poches = 32 portions
     * Then: 1 colis = 4 × 8 = 32 portions (multiplier = 32)
     */
    private function getParcelToBaseMultiplier(): float
    {
        $multiplier = 1.0;

        if ($this->subPackage instanceof PackagingLevel) {
            $multiplier *= $this->subPackage->quantity;
        }

        if ($this->consumerUnit instanceof PackagingLevel) {
            $multiplier *= $this->consumerUnit->quantity;
        }

        return $multiplier;
    }

    /**
     * Returns the multiplier to convert subPackage quantity to base units.
     *
     * If packaging is: 1 poche = 8 portions
     * Then: 1 poche = 8 portions (multiplier = 8)
     */
    private function getSubPackageToBaseMultiplier(): float
    {
        if ($this->consumerUnit instanceof PackagingLevel) {
            return $this->consumerUnit->quantity;
        }

        return 1.0;
    }
}
