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

namespace Admin\Entities\Article;

use Admin\Entities\Article\VO\Packaging;
use Admin\Entities\Event\LowStockDetected;
use Admin\Entities\FamilyLog\FamilyLog;
use Admin\Entities\Supplier\Supplier;
use Admin\Entities\Tax\Tax;
use Admin\Entities\ZoneStorage\ZoneStorage;
use Admin\Entities\ZoneStorage\ZoneStorageCollection;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\Amount;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\Quantity;

final class Article
{
    private string $slug;

    /**
     * @param array<ZoneStorage> $zoneStorages
     */
    public static function create(
        ResourceUuid $uuid,
        NameField $name,
        Supplier $supplier,
        Packaging $packaging,
        Amount $unitPrice,
        Tax $tax,
        float $minStock,
        array $zoneStorages,
        FamilyLog $familyLog,
        bool $active = true,
        ?float $quantity = 0.0
    ): self {
        $storages = new ZoneStorageCollection();
        foreach ($zoneStorages as $zoneStorage) {
            $storages->add($zoneStorage);
        }

        return new self(
            $uuid,
            $name,
            $supplier,
            $packaging,
            $unitPrice,
            $tax,
            $minStock,
            $storages,
            $familyLog,
            Quantity::fromUnit($quantity ?? 0.0),
            $active
        );
    }

    private function __construct(
        private readonly ResourceUuid $uuid,
        private NameField $name,
        private Supplier $supplier,
        private Packaging $packaging,
        private Amount $unitPrice,
        private Tax $tax,
        private float $minStock,
        private ZoneStorageCollection $zoneStorages,
        private FamilyLog $familyLog,
        private Quantity $quantity,
        private readonly bool $active
    ) {
        $this->slug = $name->slugify();
    }

    public function uuid(): ResourceUuid
    {
        return $this->uuid;
    }

    public function rename(NameField $name): void
    {
        $this->name = $name;
    }

    public function name(): NameField
    {
        return $this->name;
    }

    public function reAssignSupplier(
        Supplier $supplier,
        FamilyLog $familyLog,
        ZoneStorageCollection $zoneStorages
    ): void {
        $this->supplier = $supplier;
        $this->familyLog = $familyLog;
        $this->zoneStorages = $zoneStorages;
    }

    public function supplier(): Supplier
    {
        return $this->supplier;
    }

    public function changeStorageInformation(Packaging $packaging, float $minStock): void
    {
        $this->packaging = $packaging;
        $this->minStock = $minStock;
    }

    public function packaging(): Packaging
    {
        return $this->packaging;
    }

    public function unitPrice(): Amount
    {
        return $this->unitPrice;
    }

    public function tax(): Tax
    {
        return $this->tax;
    }

    public function changeFinancialInformation(Amount $unitPrice, Tax $tax): void
    {
        $this->unitPrice = $unitPrice;
        $this->tax = $tax;
    }

    public function minStock(): float
    {
        return $this->minStock;
    }

    public function zoneStorages(): ZoneStorageCollection
    {
        return $this->zoneStorages;
    }

    public function familyLog(): FamilyLog
    {
        return $this->familyLog;
    }

    public function quantity(): Quantity
    {
        return $this->quantity;
    }

    /**
     * Remet la quantité à la valeur issue d'un inventaire.
     * Retourne un event si quantity < minStock.
     */
    public function resetQuantity(Quantity $quantity): ?LowStockDetected
    {
        $this->quantity = $quantity;

        if ($quantity->toUnit() < $this->minStock) {
            return new LowStockDetected(
                articleUuid: $this->uuid,
                articleName: $this->name,
                currentQuantity: $quantity,
                minStock: $this->minStock,
            );
        }

        return null;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function active(): bool
    {
        return $this->active;
    }
}
