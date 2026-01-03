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

namespace Inventory\Adapters\Gateway\ORM\Entity;

use Doctrine\ORM\Mapping as ORM;
use Inventory\Entities\VO\PackagingLevel;
use Inventory\Entities\VO\PackagingSnapshot;

#[ORM\Entity]
#[ORM\Table(name: 'inventory_item_packaging')]
class InventoryItemPackaging
{
    public static function fromDomain(PackagingSnapshot $packaging, InventoryItem $inventoryItem): self
    {
        return new self(
            id: null,
            inventoryItem: $inventoryItem,
            consumerUnitLabel: $packaging->consumerUnit->unitLabel,
            consumerUnitAbbreviation: $packaging->consumerUnit->unitAbbreviation,
            consumerUnitQuantity: $packaging->consumerUnit->quantity,
            subPackageUnitLabel: $packaging->subPackage?->unitLabel,
            subPackageUnitAbbreviation: $packaging->subPackage?->unitAbbreviation,
            subPackageQuantity: $packaging->subPackage?->quantity,
            parcelUnitLabel: $packaging->parcel?->unitLabel,
            parcelUnitAbbreviation: $packaging->parcel?->unitAbbreviation,
            parcelQuantity: $packaging->parcel?->quantity,
        );
    }

    public function __construct(
        #[ORM\Id]
        #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
        #[ORM\SequenceGenerator(sequenceName: 'inventory_item_packaging_id_seq')]
        #[ORM\Column(name: 'id', type: 'integer', unique: true)]
        private ?int $id,
        #[ORM\OneToOne(targetEntity: InventoryItem::class, inversedBy: 'packaging')]
        #[ORM\JoinColumn(name: 'inventory_item_id', referencedColumnName: 'id', nullable: false)]
        private InventoryItem $inventoryItem,
        #[ORM\Column(name: 'consumer_unit_label', type: 'string', length: 50)]
        private string $consumerUnitLabel,
        #[ORM\Column(name: 'consumer_unit_abbreviation', type: 'string', length: 10)]
        private string $consumerUnitAbbreviation,
        #[ORM\Column(name: 'consumer_unit_quantity', type: 'float')]
        private float $consumerUnitQuantity,
        #[ORM\Column(name: 'sub_package_unit_label', type: 'string', length: 50, nullable: true)]
        private ?string $subPackageUnitLabel = null,
        #[ORM\Column(name: 'sub_package_unit_abbreviation', type: 'string', length: 10, nullable: true)]
        private ?string $subPackageUnitAbbreviation = null,
        #[ORM\Column(name: 'sub_package_quantity', type: 'float', nullable: true)]
        private ?float $subPackageQuantity = null,
        #[ORM\Column(name: 'parcel_unit_label', type: 'string', length: 50, nullable: true)]
        private ?string $parcelUnitLabel = null,
        #[ORM\Column(name: 'parcel_unit_abbreviation', type: 'string', length: 10, nullable: true)]
        private ?string $parcelUnitAbbreviation = null,
        #[ORM\Column(name: 'parcel_quantity', type: 'float', nullable: true)]
        private ?float $parcelQuantity = null,
    ) {
    }

    public function toDomain(): PackagingSnapshot
    {
        $consumerUnit = new PackagingLevel(
            $this->consumerUnitLabel,
            $this->consumerUnitAbbreviation,
            $this->consumerUnitQuantity,
        );

        $subPackage = null;
        if (
            $this->subPackageUnitLabel !== null
            && $this->subPackageUnitAbbreviation !== null
            && $this->subPackageQuantity !== null
        ) {
            $subPackage = new PackagingLevel(
                $this->subPackageUnitLabel,
                $this->subPackageUnitAbbreviation,
                $this->subPackageQuantity,
            );
        }

        $parcel = null;
        if (
            $this->parcelUnitLabel !== null
            && $this->parcelUnitAbbreviation !== null
            && $this->parcelQuantity !== null
        ) {
            $parcel = new PackagingLevel(
                $this->parcelUnitLabel,
                $this->parcelUnitAbbreviation,
                $this->parcelQuantity,
            );
        }

        return new PackagingSnapshot($consumerUnit, $subPackage, $parcel);
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function inventoryItem(): InventoryItem
    {
        return $this->inventoryItem;
    }
}
