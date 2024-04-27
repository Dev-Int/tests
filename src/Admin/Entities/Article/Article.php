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

use Admin\Entities\Article\VO\ArticleQuantity;
use Admin\Entities\FamilyLog\FamilyLog;
use Admin\Entities\Supplier\Supplier;
use Admin\Entities\Tax\Tax;
use Admin\Entities\ZoneStorage\ZoneStorage;
use Admin\Entities\ZoneStorage\ZoneStorageCollection;
use Shared\Entities\ResourceUuidInterface;
use Shared\Entities\VO\Amount;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\Packaging;

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
        Amount $amount,
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
            $amount,
            $tax,
            $minStock,
            $storages,
            $familyLog,
            ArticleQuantity::fromFloat($quantity ?? 0.0),
            $active
        );
    }

    private function __construct(
        private readonly ResourceUuidInterface $uuid,
        private NameField $name,
        private readonly Supplier $supplier,
        private readonly Packaging $packaging,
        private readonly Amount $amount,
        private readonly Tax $tax,
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

    public function rename(NameField $name): void
    {
        $this->name = $name;
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

    public function amount(): Amount
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

    public function zoneStorages(): ZoneStorageCollection
    {
        return $this->zoneStorages;
    }

    public function familyLog(): FamilyLog
    {
        return $this->familyLog;
    }

    public function quantity(): ArticleQuantity
    {
        return $this->quantity;
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
