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

namespace Inventory\Tests\DataBuilder;

use Inventory\Entities\InventoryItem;
use Inventory\Entities\VO\PackagingLevel;
use Inventory\Entities\VO\PackagingSnapshot;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\Amount;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\Quantity;

final class InventoryItemDataBuilder
{
    private ResourceUuid $article;
    private NameField $articleName;
    private ResourceUuid $zoneStorage;
    private int $priceCents = 1500;
    private int $theoreticalStockMilliemes = 10000;
    private int $realStockMilliemes = 0;
    private int $amountCents = 15000;
    private PackagingSnapshot $packaging;
    private ?\DateTimeImmutable $countedAt = null;
    private ?bool $reviewed = null;

    public static function defaultPackaging(): PackagingSnapshot
    {
        return new PackagingSnapshot(
            parcel: new PackagingLevel('Colis', 'cls', 1.0),
            subPackage: new PackagingLevel('Poche', 'pch', 4.0),
            consumerUnit: new PackagingLevel('Portion', 'prt', 8.0),
        );
    }

    public function __construct(
        ?ResourceUuid $article = null,
        ?ResourceUuid $zoneStorage = null,
        ?NameField $articleName = null,
    ) {
        $this->article = $article ?? ResourceUuid::generate();
        $this->zoneStorage = $zoneStorage ?? ResourceUuid::generate();
        $this->articleName = $articleName ?? NameField::fromString('Test Article');
        $this->packaging = self::defaultPackaging();
    }

    public function withPrice(int $cents): self
    {
        $this->priceCents = $cents;

        return $this;
    }

    public function withTheoreticalStock(float $units): self
    {
        $this->theoreticalStockMilliemes = (int) ($units * 1000);

        return $this;
    }

    public function withRealStock(float $units): self
    {
        $this->realStockMilliemes = (int) ($units * 1000);

        return $this;
    }

    public function withAmount(int $cents): self
    {
        $this->amountCents = $cents;

        return $this;
    }

    /**
     * Mark the item as counted (sets countedAt to current time).
     */
    public function asCounted(?\DateTimeImmutable $at = null): self
    {
        $this->countedAt = $at ?? new \DateTimeImmutable();

        return $this;
    }

    /**
     * Mark the item as reviewed.
     */
    public function asReviewed(): self
    {
        $this->reviewed = true;

        return $this;
    }

    public function build(): InventoryItem
    {
        return new InventoryItem(
            article: $this->article,
            articleName: $this->articleName,
            zoneStorage: $this->zoneStorage,
            price: Amount::fromCents($this->priceCents),
            theoreticalStock: Quantity::fromMilliemes($this->theoreticalStockMilliemes),
            realStock: Quantity::fromMilliemes($this->realStockMilliemes),
            amount: Amount::fromCents($this->amountCents),
            packaging: $this->packaging,
            countedAt: $this->countedAt,
            reviewed: $this->reviewed,
        );
    }
}
