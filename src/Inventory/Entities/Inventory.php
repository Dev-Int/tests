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

use Inventory\Entities\Exception\ArticleNotFoundInInventory;
use Inventory\Entities\Exception\CannotCancelCompletedInventory;
use Inventory\Entities\Exception\CannotRecordStockOnNonInProgressInventory;
use Inventory\Entities\Exception\CannotReviewItemOnNonReviewInventory;
use Inventory\Entities\Exception\CannotReviewItemWithoutDiscrepancy;
use Inventory\Entities\Exception\IncompleteInventoryCounting;
use Inventory\Entities\Exception\InvalidStatusTransition;
use Inventory\Entities\Exception\NoArticlesToLoad;
use Inventory\Entities\Exception\UnreviewedDiscrepancies;
use Inventory\Entities\ReadModel\ArticleData;
use Inventory\Entities\VO\Article;
use Inventory\Entities\VO\InventoryDate;
use Inventory\Entities\VO\InventoryStatus;
use Inventory\Entities\VO\ZoneStorage;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\Amount;

final class Inventory
{
    /**
     * @param array<ZoneStorage> $zoneStorages
     */
    public static function create(ResourceUuid $uuid, array $zoneStorages, InventoryDate $date): self
    {
        return new self(
            uuid: $uuid,
            zoneStorages: $zoneStorages,
            date: $date,
            status: InventoryStatus::DRAFT,
            amount: Amount::zero(),
            discrepancyAmount: Amount::zero(),
            createdAt: ClockFactory::clock()->now(),
            updatedAt: ClockFactory::clock()->now(),
            statusUpdatedAt: null,
            items: new InventoryItemCollection(),
        );
    }

    /**
     * @param array<ZoneStorage> $zoneStorages
     */
    public static function reconstitute(
        ResourceUuid $uuid,
        array $zoneStorages,
        InventoryDate $date,
        InventoryStatus $status,
        Amount $amount,
        Amount $discrepancyAmount,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
        ?\DateTimeImmutable $statusUpdatedAt,
    ): self {
        return new self(
            uuid: $uuid,
            zoneStorages: $zoneStorages,
            date: $date,
            status: $status,
            amount: $amount,
            discrepancyAmount: $discrepancyAmount,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            statusUpdatedAt: $statusUpdatedAt,
            items: new InventoryItemCollection()
        );
    }

    /**
     * @param array<ZoneStorage> $zoneStorages
     */
    private function __construct(
        private readonly ResourceUuid $uuid,
        private readonly array $zoneStorages,
        private readonly InventoryDate $date,
        private InventoryStatus $status,
        private readonly Amount $amount,
        private Amount $discrepancyAmount,
        private readonly \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
        private ?\DateTimeImmutable $statusUpdatedAt,
        private InventoryItemCollection $items,
    ) {
    }

    public function uuid(): ResourceUuid
    {
        return $this->uuid;
    }

    /**
     * @return array<ZoneStorage>
     */
    public function zoneStorages(): array
    {
        return $this->zoneStorages;
    }

    public function date(): InventoryDate
    {
        return $this->date;
    }

    public function status(): InventoryStatus
    {
        return $this->status;
    }

    public function amount(): Amount
    {
        return $this->amount;
    }

    public function discrepancyAmount(): Amount
    {
        return $this->discrepancyAmount;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function statusUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->statusUpdatedAt;
    }

    public function items(): InventoryItemCollection
    {
        return $this->items;
    }

    public function areAllItemsCounted(): bool
    {
        return $this->items->getZonesWithUncountedItems() === [];
    }

    public function hasUnreviewedDiscrepancies(): bool
    {
        return $this->items->hasUnreviewedDiscrepancies();
    }

    public function addItem(InventoryItem $itemDomain): void
    {
        $this->items->add($itemDomain);
    }

    public function isDraft(): bool
    {
        return InventoryStatus::DRAFT === $this->status;
    }

    public function clearItems(): void
    {
        $this->items = new InventoryItemCollection();
    }

    /**
     * @param iterable<Article> $articles
     *
     * @throws NoArticlesToLoad
     */
    public function loadArticles(iterable $articles): void
    {
        $this->clearItems();

        $count = 0;
        foreach ($articles as $article) {
            $this->addItem(InventoryItem::createFromArticle($article));
            ++$count;
        }

        if ($count === 0) {
            throw new NoArticlesToLoad();
        }
    }

    /**
     * Démarre le traitement de l'inventaire (DRAFT → IN_PROGRESS).
     */
    public function startProcessing(): void
    {
        if (InventoryStatus::DRAFT !== $this->status) {
            throw new InvalidStatusTransition(fromStatus: $this->status, toStatus: InventoryStatus::IN_PROGRESS);
        }
        $this->status = InventoryStatus::IN_PROGRESS;
        $this->statusUpdatedAt = ClockFactory::clock()->now();
    }

    /**
     * Soumet l'inventaire pour révision (IN_PROGRESS → REVIEW).
     */
    public function submitForReview(): void
    {
        if (InventoryStatus::IN_PROGRESS !== $this->status) {
            throw new InvalidStatusTransition(fromStatus: $this->status, toStatus: InventoryStatus::REVIEW);
        }
        $this->status = InventoryStatus::REVIEW;
        $this->statusUpdatedAt = ClockFactory::clock()->now();
    }

    /**
     * Termine le comptage et soumet pour révision (IN_PROGRESS → REVIEW).
     * Valide que TOUS les items ont été comptés avant d'autoriser la transition.
     *
     * @throws InvalidStatusTransition     si l'inventaire n'est pas IN_PROGRESS
     * @throws IncompleteInventoryCounting si certains items n'ont pas été comptés
     */
    public function finishCounting(): void
    {
        if (InventoryStatus::IN_PROGRESS !== $this->status) {
            throw new InvalidStatusTransition(fromStatus: $this->status, toStatus: InventoryStatus::REVIEW);
        }

        $zonesWithUncountedItems = $this->items->getZonesWithUncountedItems();
        if ($zonesWithUncountedItems !== []) {
            throw new IncompleteInventoryCounting($zonesWithUncountedItems);
        }

        $this->status = InventoryStatus::REVIEW;
        $this->statusUpdatedAt = ClockFactory::clock()->now();
    }

    /**
     * Renvoie l'inventaire en traitement pour corrections (REVIEW → IN_PROGRESS).
     */
    public function sendBackToProcessing(): void
    {
        if (InventoryStatus::REVIEW !== $this->status) {
            throw new InvalidStatusTransition(fromStatus: $this->status, toStatus: InventoryStatus::IN_PROGRESS);
        }
        $this->status = InventoryStatus::IN_PROGRESS;
        $this->statusUpdatedAt = ClockFactory::clock()->now();
    }

    /**
     * Finalise l'inventaire (REVIEW → COMPLETED).
     *
     * @throws InvalidStatusTransition if inventory is not in REVIEW status
     * @throws UnreviewedDiscrepancies if some discrepancies have not been reviewed
     */
    public function complete(Amount $discrepancyAmount): void
    {
        if (InventoryStatus::REVIEW !== $this->status) {
            throw new InvalidStatusTransition(fromStatus: $this->status, toStatus: InventoryStatus::COMPLETED);
        }

        $unreviewedDiscrepancies = $this->items->getUnreviewedDiscrepancies();
        if ($unreviewedDiscrepancies !== []) {
            throw new UnreviewedDiscrepancies($unreviewedDiscrepancies);
        }

        $this->discrepancyAmount = $discrepancyAmount;
        $this->status = InventoryStatus::COMPLETED;
        $now = ClockFactory::clock()->now();
        $this->statusUpdatedAt = $now;
        $this->updatedAt = $now;
    }

    /**
     * Annule l'inventaire (DRAFT | IN_PROGRESS | REVIEW → CANCELLED).
     *
     * @throws CannotCancelCompletedInventory si l'inventaire est déjà finalisé ou annulé
     */
    public function cancel(): void
    {
        if (!$this->status->isCancellable()) {
            throw new CannotCancelCompletedInventory($this->status);
        }

        $this->status = InventoryStatus::CANCELLED;
        $now = ClockFactory::clock()->now();
        $this->statusUpdatedAt = $now;
        $this->updatedAt = $now;
    }

    /**
     * Enregistre le stock réel compté pour les articles d'une zone.
     *
     * @param array<ArticleData> $articlesData
     *
     * @return array<InventoryItem>
     *
     * @throws CannotRecordStockOnNonInProgressInventory
     * @throws ArticleNotFoundInInventory
     */
    public function recordRealStocks(array $articlesData, ResourceUuid $zoneStorageUuid): array
    {
        if (InventoryStatus::IN_PROGRESS !== $this->status) {
            throw new CannotRecordStockOnNonInProgressInventory($this->status);
        }

        $updatedItems = array_map(function (ArticleData $articleData) use ($zoneStorageUuid): InventoryItem {
            $item = $this->items->findByArticleAndZone($articleData->articleUuid, $zoneStorageUuid);

            if (!$item instanceof InventoryItem) {
                throw new ArticleNotFoundInInventory($articleData->articleUuid);
            }

            return $item->withRealStock($articleData->realStock, $articleData->realStockComponents);
        }, $articlesData);

        $this->items->replace($updatedItems);

        return $updatedItems;
    }

    /**
     * Marque plusieurs items avec écart comme "révisés".
     *
     * @param array<array{articleUuid: ResourceUuid, zoneStorageUuid: ResourceUuid}> $itemIdentifiers
     *
     * @return array<InventoryItem> Items marqués comme révisés
     *
     * @throws CannotReviewItemOnNonReviewInventory
     * @throws ArticleNotFoundInInventory
     * @throws CannotReviewItemWithoutDiscrepancy
     */
    public function reviewDiscrepancies(array $itemIdentifiers): array
    {
        if (InventoryStatus::REVIEW !== $this->status) {
            throw new CannotReviewItemOnNonReviewInventory($this->status);
        }

        $reviewedItems = array_map(function (array $identifier): InventoryItem {
            $articleUuid = $identifier['articleUuid'];
            $zoneStorageUuid = $identifier['zoneStorageUuid'];

            $item = $this->items->findByArticleAndZone($articleUuid, $zoneStorageUuid);

            if (!$item instanceof InventoryItem) {
                throw new ArticleNotFoundInInventory($articleUuid);
            }

            if (!$item->hasDiscrepancy()) {
                throw new CannotReviewItemWithoutDiscrepancy($articleUuid, $zoneStorageUuid);
            }

            return $item->withReviewed(true);
        }, $itemIdentifiers);

        $this->items->replace($reviewedItems);

        return $reviewedItems;
    }
}
