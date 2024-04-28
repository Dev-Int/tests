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

namespace Admin\Adapters\Gateway\ORM\Entity;

use Admin\Adapters\Gateway\ORM\Repository\DoctrineArticleRepository;
use Admin\Entities\Article\Article as ArticleDomain;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\Amount;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\Packaging;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: DoctrineArticleRepository::class)]
#[UniqueEntity('name')]
final class Article
{
    #[ORM\Id]
    #[ORM\Column(name: 'uuid', type: 'guid')]
    private string $uuid;
    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    private string $name;
    #[ORM\ManyToOne(targetEntity: Supplier::class)]
    #[ORM\JoinColumn(name: 'supplier_id', referencedColumnName: 'uuid')]
    private Supplier $supplier;

    /**
     * @var array<array{string, float}|null>
     */
    #[ORM\Column(name: 'packaging', type: 'json')]
    private array $packaging = [];
    #[ORM\Column(name: 'amount', type: 'integer')]
    private int $amount;
    #[ORM\ManyToOne(targetEntity: Tax::class)]
    #[ORM\JoinColumn(name: 'tax_id', referencedColumnName: 'uuid')]
    private Tax $tax;
    #[ORM\Column(name: 'min_stock', type: 'float')]
    private float $minStock;

    /**
     * @var array<ZoneStorage>|Collection<ZoneStorage>
     */
    #[ORM\ManyToMany(targetEntity: ZoneStorage::class)]
    #[ORM\JoinTable(name: 'articles_zone_storages')]
    #[ORM\JoinColumn(name: 'article_id', referencedColumnName: 'uuid')]
    #[ORM\InverseJoinColumn(name: 'zone_storage_id', referencedColumnName: 'uuid')]
    private array|Collection $zoneStorages;
    #[ORM\ManyToOne(targetEntity: FamilyLog::class)]
    #[ORM\JoinColumn(name: 'family_log_id', referencedColumnName: 'uuid')]
    private FamilyLog $familyLog;
    #[ORM\Column(name: 'quantity', type: 'float', scale: 3)]
    private float $quantity;
    #[ORM\Column(name: 'slug', type: 'string')]
    private string $slug;
    #[ORM\Column(name: 'active', type: 'boolean')]
    private bool $active;

    public function __construct()
    {
        $this->zoneStorages = new ArrayCollection();
    }

    public function fromDomain(
        ArticleDomain $article,
        Supplier $supplier,
        Tax $tax,
        Collection $zoneStorages,
        FamilyLog $familyLog
    ): self {
        $this->uuid = $article->uuid()->toString();
        $this->name = $article->name()->toString();
        $this->supplier = $supplier;
        $this->packaging = [
            $article->packaging()->parcel(),
            $article->packaging()->subPackage(),
            $article->packaging()->consumerUnit(),
        ];
        $this->amount = $article->amount()->toInt();
        $this->tax = $tax;
        $this->minStock = $article->minStock();
        $this->zoneStorages = $zoneStorages;
        $this->familyLog = $familyLog;
        $this->quantity = $article->quantity()->toFloat();
        $this->active = $article->active();
        $this->slug = $article->slug();

        return $this;
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
            Packaging::fromArray($this->packaging),
            Amount::fromInt($this->amount),
            $this->tax->toDomain(),
            $this->minStock,
            $zoneStorages,
            $this->familyLog->toDomain()
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

    /**
     * @return array<array{string, float}|null>
     */
    public function packaging(): array
    {
        return $this->packaging;
    }

    public function amount(): int
    {
        return $this->amount;
    }

    public function tax(): Tax
    {
        return $this->tax;
    }

    public function minStock(): float
    {
        return $this->minStock;
    }

    /**
     * @param ArrayCollection<ZoneStorage> $zoneStorages
     */
    public function setZoneStorages(ArrayCollection $zoneStorages): self
    {
        $this->zoneStorages = $zoneStorages;

        return $this;
    }

    /**
     * @return array<ZoneStorage>|Collection<ZoneStorage>
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
