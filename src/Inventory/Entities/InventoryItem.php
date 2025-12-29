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

namespace Inventory\Entities;

use Inventory\Entities\VO\Article;
use Inventory\Entities\VO\PackagingSnapshot;
use Inventory\Entities\VO\RealStockComponents;
use Inventory\Entities\VO\StockDifference;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\Amount;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\Quantity;

final readonly class InventoryItem
{
    public static function createFromArticle(Article $article): self
    {
        return new self(
            article: $article->uuid,
            articleName: $article->name,
            zoneStorage: $article->zoneStorageUuid,
            price: $article->unitPrice,
            theoreticalStock: $article->quantity,
            realStock: Quantity::fromMilliemes(0),
            realStockComponents: RealStockComponents::zero(),
            amount: $article->unitPrice->computeQuantity($article->quantity),
            packaging: $article->packaging,
        );
    }

    /**
     * @param Amount                  $amount              Snapshot of theoretical value at creation (price × theoreticalStock).
     *                                                     This is NOT recalculated when realStock changes.
     *                                                     Real value after counting = price × realStock.
     * @param PackagingSnapshot       $packaging           snapshot of article packaging at inventory creation
     * @param RealStockComponents     $realStockComponents the decomposed quantities for each packaging level
     * @param \DateTimeImmutable|null $countedAt           timestamp when the item was counted, null if not yet counted
     * @param string|null             $reviewNotes         prepared for future iteration
     * @param string|null             $actionPlan          prepared for future iteration
     */
    public function __construct(
        private ResourceUuid $article,
        private NameField $articleName,
        private ResourceUuid $zoneStorage,
        private Amount $price,
        private Quantity $theoreticalStock,
        private Quantity $realStock,
        private RealStockComponents $realStockComponents,
        private Amount $amount,
        private PackagingSnapshot $packaging,
        private ?\DateTimeImmutable $countedAt = null,
        private bool $reviewed = false,
        private ?string $reviewNotes = null,
        private ?string $actionPlan = null,
    ) {
    }

    public function article(): ResourceUuid
    {
        return $this->article;
    }

    public function articleName(): NameField
    {
        return $this->articleName;
    }

    public function zoneStorage(): ResourceUuid
    {
        return $this->zoneStorage;
    }

    public function identifier(): string
    {
        return "{$this->article->toString()}_{$this->zoneStorage->toString()}";
    }

    public function price(): Amount
    {
        return $this->price;
    }

    public function theoreticalStock(): Quantity
    {
        return $this->theoreticalStock;
    }

    public function realStock(): Quantity
    {
        return $this->realStock;
    }

    public function realStockComponents(): RealStockComponents
    {
        return $this->realStockComponents;
    }

    public function amount(): Amount
    {
        return $this->amount;
    }

    public function packaging(): PackagingSnapshot
    {
        return $this->packaging;
    }

    public function isFor(ResourceUuid $articleUuid, ResourceUuid $zoneStorageUuid): bool
    {
        return $this->article->toString() === $articleUuid->toString()
            && $this->zoneStorage->toString() === $zoneStorageUuid->toString();
    }

    public function isForArticle(ResourceUuid $articleUuid): bool
    {
        return $this->article->toString() === $articleUuid->toString();
    }

    public function isForZone(ResourceUuid $zoneStorageUuid): bool
    {
        return $this->zoneStorage->toString() === $zoneStorageUuid->toString();
    }

    public function withRealStock(Quantity $realStock, RealStockComponents $components): self
    {
        return new self(
            article: $this->article,
            articleName: $this->articleName,
            zoneStorage: $this->zoneStorage,
            price: $this->price,
            theoreticalStock: $this->theoreticalStock,
            realStock: $realStock,
            realStockComponents: $components,
            amount: $this->amount,
            packaging: $this->packaging,
            countedAt: ClockFactory::clock()->now(),
            reviewed: $this->reviewed,
            reviewNotes: $this->reviewNotes,
            actionPlan: $this->actionPlan,
        );
    }

    public function countedAt(): ?\DateTimeImmutable
    {
        return $this->countedAt;
    }

    public function hasBeenCounted(): bool
    {
        return $this->countedAt instanceof \DateTimeImmutable;
    }

    public function calculateDifference(): StockDifference
    {
        return StockDifference::calculate($this->realStock, $this->theoreticalStock);
    }

    public function isReviewed(): bool
    {
        return $this->reviewed;
    }

    public function hasDiscrepancy(): bool
    {
        return !$this->calculateDifference()->isZero();
    }

    public function withReviewed(bool $reviewed): self
    {
        return new self(
            article: $this->article,
            articleName: $this->articleName,
            zoneStorage: $this->zoneStorage,
            price: $this->price,
            theoreticalStock: $this->theoreticalStock,
            realStock: $this->realStock,
            realStockComponents: $this->realStockComponents,
            amount: $this->amount,
            packaging: $this->packaging,
            countedAt: $this->countedAt,
            reviewed: $reviewed,
            reviewNotes: $this->reviewNotes,
            actionPlan: $this->actionPlan,
        );
    }
}
