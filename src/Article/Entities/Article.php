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

namespace Article\Entities;

use Article\Entities\Component\Supplier;
use Article\Entities\Component\ZoneStorage;
use Article\Entities\Component\ZoneStorageCollection;
use Article\Entities\VO\Amount;
use Article\Entities\VO\ArticleQuantity;
use Article\Entities\VO\Packaging;
use Shared\Entities\ResourceUuidInterface;
use Shared\Entities\VO\FamilyLog;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\Taxes;

final class Article
{
    private string $slug;

    /**
     * @param array<ZoneStorage> $zoneStorages
     */
    public static function create(
        ResourceUuidInterface $uuid,
        NameField $name,
        Supplier $supplier,
        Packaging $packaging,
        Amount $price,
        Taxes $taxes,
        float $minStock,
        array $zoneStorages,
        FamilyLog $familyLog,
        ?float $quantity = null,
        ?bool $active = true
    ): self {
        if (null === $active) {
            $active = true;
        }
        $storages = new ZoneStorageCollection();
        foreach ($zoneStorages as $zoneStorage) {
            $storages->add($zoneStorage);
        }

        return new self(
            $uuid,
            $name,
            $supplier,
            $packaging,
            $price,
            $taxes,
            $minStock,
            $storages,
            $familyLog,
            ArticleQuantity::fromFloat($quantity),
            $active
        );
    }

    private function __construct(
        private readonly ResourceUuidInterface $uuid,
        private NameField $name,
        private readonly Supplier $supplier,
        private readonly Packaging $packaging,
        private readonly Amount $price,
        private readonly Taxes $taxes,
        private readonly float $minStock,
        private readonly ZoneStorageCollection $zoneStorages,
        private readonly FamilyLog $familyLog,
        private readonly ArticleQuantity $quantity,
        private readonly bool $active
    ) {
        $this->slug = $name->slugify();
    }

    public function uuid(): ResourceUuidInterface
    {
        return $this->uuid;
    }

    public function name(): NameField
    {
        return $this->name;
    }

    public function supplier(): Supplier
    {
        return $this->supplier;
    }

    public function packaging(): Packaging
    {
        return $this->packaging;
    }

    public function price(): Amount
    {
        return $this->price;
    }

    public function taxes(): Taxes
    {
        return $this->taxes;
    }

    public function quantity(): ArticleQuantity
    {
        return $this->quantity;
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

    public function isActive(): bool
    {
        return $this->active;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function renameArticle(NameField $name): void
    {
        $this->name = $name;
        $this->slug = $name->slugify();
    }
}
