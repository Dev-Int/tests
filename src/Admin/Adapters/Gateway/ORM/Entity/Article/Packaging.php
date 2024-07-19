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
        #[ORM\OneToOne(inversedBy: 'packaging', targetEntity: Article::class)]
        #[ORM\JoinColumn(name: 'article_id', referencedColumnName: 'uuid', nullable: false)]
        private Article $article,
        #[ORM\ManyToOne(targetEntity: Unit::class)]
        #[ORM\JoinColumn(name: 'parcel_unit_id', referencedColumnName: 'uuid', nullable: false)]
        private Unit $parcelUnit,
        #[ORM\Column(name: 'parcel_quantity', type: 'float', nullable: false)]
        private float $parcelQuantity,
        #[ORM\ManyToOne(targetEntity: Unit::class)]
        #[ORM\JoinColumn(name: 'sub_package_unit_id', referencedColumnName: 'uuid', nullable: true)]
        private ?Unit $subPackageUnit,
        #[ORM\Column(name: 'sub_package_quantity', type: 'float', nullable: true)]
        private ?float $subPackageQuantity,
        #[ORM\ManyToOne(targetEntity: Unit::class)]
        #[ORM\JoinColumn(name: 'consume_unit_unit_id', referencedColumnName: 'uuid', nullable: true)]
        private ?Unit $consumeUnitUnit,
        #[ORM\Column(name: 'consume_unit_quantity', type: 'float', nullable: true)]
        private ?float $consumeUnitQuantity,
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

    public function article(): Article
    {
        return $this->article;
    }

    public function setArticle(Article $article): self
    {
        $this->article = $article;
        $article->setPackaging($this);

        return $this;
    }

    public function parcelUnit(): Unit
    {
        return $this->parcelUnit;
    }

    public function setParcelUnit(Unit $parcelUnit): self
    {
        $this->parcelUnit = $parcelUnit;

        return $this;
    }

    public function parcelQuantity(): float
    {
        return $this->parcelQuantity;
    }

    public function setParcelQuantity(float $parcelQuantity): self
    {
        $this->parcelQuantity = $parcelQuantity;

        return $this;
    }

    public function subPackageUnit(): ?Unit
    {
        return $this->subPackageUnit;
    }

    public function setSubPackageUnit(?Unit $subPackageUnit): self
    {
        $this->subPackageUnit = $subPackageUnit;

        return $this;
    }

    public function subPackageQuantity(): ?float
    {
        return $this->subPackageQuantity;
    }

    public function setSubPackageQuantity(?float $subPackageQuantity): self
    {
        $this->subPackageQuantity = $subPackageQuantity;

        return $this;
    }

    public function consumeUnitUnit(): ?Unit
    {
        return $this->consumeUnitUnit;
    }

    public function setConsumeUnitUnit(?Unit $consumeUnitUnit): self
    {
        $this->consumeUnitUnit = $consumeUnitUnit;

        return $this;
    }

    public function consumeUnitQuantity(): ?float
    {
        return $this->consumeUnitQuantity;
    }

    public function setConsumeUnitQuantity(?float $consumeUnitQuantity): self
    {
        $this->consumeUnitQuantity = $consumeUnitQuantity;

        return $this;
    }

    /**
     * @return array{UnitDomain, float}|null
     */
    private function getPackageInDomainFormat(?Unit $unit, ?float $quantity): ?array
    {
        return $unit instanceof Unit && $quantity !== null ? [$unit->toDomain(), $quantity] : null;
    }
}
