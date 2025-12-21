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

use Admin\Adapters\Gateway\ORM\Entity\FamilyLog\FamilyLog;
use Admin\Adapters\Gateway\ORM\Entity\Supplier;
use Admin\Adapters\Gateway\ORM\Entity\Tax;
use Admin\Adapters\Gateway\ORM\Entity\ZoneStorage;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineArticleRepository;
use Admin\Entities\Article\Article as ArticleDomain;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\Amount;
use Shared\Entities\VO\NameField;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: DoctrineArticleRepository::class)]
#[UniqueEntity(fields: 'name')]
class Article
{
    #[ORM\OneToOne(mappedBy: 'article', targetEntity: Packaging::class, cascade: ['persist', 'remove'])]
    private Packaging $packaging;

    /**
     * @param Collection<array-key, ZoneStorage> $zoneStorages
     */
    public static function fromDomain(
        ArticleDomain $article,
        Supplier $supplier,
        Tax $tax,
        Collection $zoneStorages,
        FamilyLog $familyLog
    ): self {
        return new self(
            $article->uuid()->toString(),
            $article->name()->toString(),
            $supplier,
            $article->unitPrice()->toInt(),
            $tax,
            $article->minStock(),
            $zoneStorages,
            $familyLog,
            $article->quantity()->toUnit(),
            $article->slug(),
            $article->active(),
        );
    }

    /**
     * @param array<ZoneStorage>|Collection<array-key, ZoneStorage> $zoneStorages
     */
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(name: 'uuid', type: 'guid')]
        private readonly string $uuid,
        #[ORM\Column(name: 'name', type: 'string', length: 255)]
        private string $name,
        #[ORM\ManyToOne(targetEntity: Supplier::class)]
        #[ORM\JoinColumn(name: 'supplier_id', referencedColumnName: 'uuid')]
        private Supplier $supplier,
        #[ORM\Column(name: 'unit_price', type: 'integer')]
        private int $unitPrice,
        #[ORM\ManyToOne(targetEntity: Tax::class)]
        #[ORM\JoinColumn(name: 'tax_id', referencedColumnName: 'uuid')]
        private Tax $tax,
        #[ORM\Column(name: 'min_stock', type: 'float')]
        private float $minStock,
        #[ORM\ManyToMany(targetEntity: ZoneStorage::class)]
        #[ORM\JoinTable(name: 'articles_zone_storages')]
        #[ORM\JoinColumn(name: 'article_id', referencedColumnName: 'uuid')]
        #[ORM\InverseJoinColumn(name: 'zone_storage_id', referencedColumnName: 'uuid')]
        private array|Collection $zoneStorages,
        #[ORM\ManyToOne(targetEntity: FamilyLog::class)]
        #[ORM\JoinColumn(name: 'family_log_id', referencedColumnName: 'uuid')]
        private FamilyLog $familyLog,
        #[ORM\Column(name: 'quantity', type: 'float', scale: 3)]
        private readonly float $quantity,
        #[ORM\Column(name: 'slug', type: 'string')]
        private string $slug,
        #[ORM\Column(name: 'active', type: 'boolean')]
        private readonly bool $active
    ) {
    }

    public function toDomain(): ArticleDomain
    {
        $zoneStorages = [];
        foreach ($this->zoneStorages as $zoneStorage) {
            $zoneStorages[] = $zoneStorage->toDomain();
        }

        return ArticleDomain::create(
            ResourceUuid::fromString($this->uuid),
            NameField::fromString($this->name),
            $this->supplier->toDomain(),
            $this->packaging->toDomain(),
            Amount::fromInt($this->unitPrice),
            $this->tax->toDomain(),
            $this->minStock,
            $zoneStorages,
            $this->familyLog->toDomain(),
            $this->active(),
            $this->quantity
        );
    }

    /**
     * @codeCoverageIgnore
     */
    public function uuid(): string
    {
        return $this->uuid;
    }

    public function setName(string $toString): self
    {
        $this->name = $toString;

        return $this;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function setSupplier(Supplier $supplier): self
    {
        $this->supplier = $supplier;

        return $this;
    }

    public function supplier(): Supplier
    {
        return $this->supplier;
    }

    public function setPackaging(Packaging $packaging): self
    {
        $this->packaging = $packaging;

        return $this;
    }

    public function packaging(): Packaging
    {
        return $this->packaging;
    }

    public function setUnitPrice(int $unitPrice): self
    {
        $this->unitPrice = $unitPrice;

        return $this;
    }

    public function unitPrice(): int
    {
        return $this->unitPrice;
    }

    public function setTax(Tax $tax): self
    {
        $this->tax = $tax;

        return $this;
    }

    public function tax(): Tax
    {
        return $this->tax;
    }

    public function setMinStock(float $minStock): self
    {
        $this->minStock = $minStock;

        return $this;
    }

    public function minStock(): float
    {
        return $this->minStock;
    }

    /**
     * @param ArrayCollection<array-key, ZoneStorage> $zoneStorages
     */
    public function setZoneStorages(ArrayCollection $zoneStorages): self
    {
        $this->zoneStorages = $zoneStorages;

        return $this;
    }

    /**
     * @return array<ZoneStorage>|Collection<array-key, ZoneStorage>
     */
    public function zoneStorages(): array|Collection
    {
        return $this->zoneStorages;
    }

    public function setFamilyLog(FamilyLog $familyLog): self
    {
        $this->familyLog = $familyLog;

        return $this;
    }

    public function familyLog(): FamilyLog
    {
        return $this->familyLog;
    }

    public function quantity(): float
    {
        return $this->quantity;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function active(): bool
    {
        return $this->active;
    }
}
