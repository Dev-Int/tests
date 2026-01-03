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
 * Hierarchy: consumerUnit (required) → subPackage (optional) → parcel (optional)
 *
 * Example: 32 portions = 4 poches = 1 colis
 * - consumerUnit: {label: "portion", abbr: "prt", qty: 1} (base unit)
 * - subPackage: {label: "poche", abbr: "pch", qty: 8} (8 portions per poche)
 * - parcel: {label: "colis", abbr: "cls", qty: 4} (4 poches per colis)
 */
final readonly class PackagingSnapshot
{
    public function __construct(
        public PackagingLevel $consumerUnit,
        public ?PackagingLevel $subPackage = null,
        public ?PackagingLevel $parcel = null,
    ) {
    }

    /**
     * @return list<PackagingLevel>
     */
    public function levels(): array
    {
        return array_values(array_filter([
            $this->consumerUnit,
            $this->subPackage,
            $this->parcel,
        ]));
    }

    public function hasMultipleLevels(): bool
    {
        return $this->subPackage instanceof PackagingLevel || $this->parcel instanceof PackagingLevel;
    }

    /**
     * Calculates total quantity in base units from multi-level components.
     *
     * For packaging: 32 portions = 4 poches (8 portions each) = 1 colis (4 poches)
     * Components: 5 portions + 3 poches + 2 colis
     *
     * Calculation:
     * - 5 portions = 5 portions (direct)
     * - 3 poches × 8 portions/poche = 24 portions
     * - 2 colis × 4 poches/colis × 8 portions/poche = 64 portions
     * - Total = 5 + 24 + 64 = 93 portions
     */
    public function calculateTotalFromComponents(RealStockComponents $components): Quantity
    {
        $total = 0.0;

        // ConsumerUnit contribution: direct base units (always present)
        $total += $components->consumerUnit->toUnit();

        // SubPackage contribution: subPackageQty × subPackage.quantity (portions per subPackage)
        if ($this->subPackage instanceof PackagingLevel) {
            $total += $components->subPackage->toUnit() * $this->subPackage->quantity;
        }

        // Parcel contribution: parcelQty × parcel.quantity × subPackage.quantity
        if ($this->parcel instanceof PackagingLevel) {
            $total += $components->parcel->toUnit() * $this->getParcelToBaseMultiplier();
        }

        return Quantity::fromUnit($total);
    }

    /**
     * Returns the multiplier to convert parcel quantity to base units.
     *
     * If packaging is: 1 colis = 4 poches × 8 portions = 32 portions
     * Then: multiplier = 4 × 8 = 32
     */
    private function getParcelToBaseMultiplier(): float
    {
        if (!$this->parcel instanceof PackagingLevel) {
            return 0.0; // Should never happen - method called only when parcel exists
        }

        // parcel->quantity is number of subPackages per parcel (e.g., 4 poches per colis)
        $multiplier = $this->parcel->quantity;

        // subPackage->quantity is number of consumerUnits per subPackage (e.g., 8 portions per poche)
        if ($this->subPackage instanceof PackagingLevel) {
            $multiplier *= $this->subPackage->quantity;
        }

        return $multiplier;
    }
}
