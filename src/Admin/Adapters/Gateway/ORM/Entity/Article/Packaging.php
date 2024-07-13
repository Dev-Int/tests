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

namespace Admin\Adapters\Gateway\ORM\Entity\Article;

use Admin\Adapters\Gateway\ORM\Entity\Unit;
use Admin\Entities\Unit\Unit as UnitDomain;
use Doctrine\ORM\Mapping as ORM;
use Shared\Entities\VO\Packaging as PackagingDomain;

#[ORM\Entity]
class Packaging
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'packaging_seq_id', allocationSize: 1, initialValue: 1)]
    #[ORM\Column(type: 'integer')]
    private int $id;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Unit::class)]
        #[ORM\JoinColumn(name: 'parcel_unit_id', referencedColumnName: 'uuid')]
        private readonly Unit $parcelUnit,
        #[ORM\Column(name: 'parcel_quantity', type: 'float')]
        private readonly float $parcelQuantity,
        #[ORM\ManyToOne(targetEntity: Unit::class)]
        #[ORM\JoinColumn(name: 'sub_package_unit_id', referencedColumnName: 'uuid', nullable: true)]
        private readonly ?Unit $subPackageUnit,
        #[ORM\Column(name: 'sub_package_quantity', type: 'float', nullable: true)]
        private readonly ?float $subPackageQuantity,
        #[ORM\ManyToOne(targetEntity: Unit::class)]
        #[ORM\JoinColumn(name: 'consume_unit_unit_id', referencedColumnName: 'uuid', nullable: true)]
        private readonly ?Unit $consumeUnitUnit,
        #[ORM\Column(name: 'consume_unit_quantity', type: 'float', nullable: true)]
        private readonly ?float $consumeUnitQuantity,
    ) {
    }

    public function toDomain(): PackagingDomain
    {
        return PackagingDomain::fromArray([
            [$this->parcelUnit->toDomain(), $this->parcelQuantity],
            $this->getPackageInDomainFormat($this->subPackageUnit, $this->subPackageQuantity),
            $this->getPackageInDomainFormat($this->consumeUnitUnit, $this->consumeUnitQuantity),
        ]);
    }

    public function id(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function parcelUnit(): Unit
    {
        return $this->parcelUnit;
    }

    public function parcelQuantity(): float
    {
        return $this->parcelQuantity;
    }

    public function subPackageUnit(): ?Unit
    {
        return $this->subPackageUnit;
    }

    public function subPackageQuantity(): ?float
    {
        return $this->subPackageQuantity;
    }

    public function consumeUnitUnit(): ?Unit
    {
        return $this->consumeUnitUnit;
    }

    public function consumeUnitQuantity(): ?float
    {
        return $this->consumeUnitQuantity;
    }

    /**
     * @return array{UnitDomain, float}|null
     */
    private function getPackageInDomainFormat(?Unit $unit, ?float $quantity): ?array
    {
        return $unit instanceof Unit && $quantity !== null ? [$unit->toDomain(), $quantity] : null;
    }
}
