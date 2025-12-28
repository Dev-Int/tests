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

namespace Inventory\Tests\Entities;

use Inventory\Entities\Exception\ArticleNotFoundInInventory;
use Inventory\Entities\Exception\CannotReviewItemOnNonReviewInventory;
use Inventory\Entities\Exception\CannotReviewItemWithoutDiscrepancy;
use Inventory\Entities\VO\InventoryStatus;
use Inventory\Tests\Factory\InventoryFakerFactory;
use Inventory\Tests\Factory\InventoryItemFakerFactory;
use PHPUnit\Framework\TestCase;
use Shared\Entities\Exception\DomainException;
use Shared\Entities\ResourceUuid;

/**
 * @group unitTest
 *
 * @covers \Inventory\Entities\Inventory
 */
final class InventoryReviewDiscrepanciesTest extends TestCase
{
    private InventoryFakerFactory $inventoryFactory;
    private InventoryItemFakerFactory $itemFactory;

    protected function setUp(): void
    {
        $this->inventoryFactory = new InventoryFakerFactory();
        $this->itemFactory = new InventoryItemFakerFactory();
    }

    public function testReviewDiscrepanciesMarksItemsAsReviewed(): void
    {
        // Arrange
        $articleUuid1 = ResourceUuid::generate();
        $articleUuid2 = ResourceUuid::generate();
        $zoneStorageUuid = ResourceUuid::generate();

        $inventory = $this->inventoryFactory->createReviewed()->build();
        // Item with discrepancy (theoretical: 10, real: 8)
        $inventory->addItem(
            $this->itemFactory->createWithPreciseStocks(10.0, 8.0, $zoneStorageUuid)
                ->build()
        );
        // Another item with discrepancy for bulk test
        $inventory->addItem(
            $this->itemFactory->create($articleUuid1, $zoneStorageUuid)
                ->withTheoreticalStock(5.0)
                ->withRealStock(7.0)
                ->asCounted()
                ->build()
        );
        // Item without discrepancy (should not be reviewed)
        $inventory->addItem(
            $this->itemFactory->create($articleUuid2, $zoneStorageUuid)
                ->withTheoreticalStock(10.0)
                ->withRealStock(10.0)
                ->asCounted()
                ->build()
        );

        $itemIdentifiers = [
            ['articleUuid' => $articleUuid1, 'zoneStorageUuid' => $zoneStorageUuid],
        ];

        // Act
        $reviewedItems = $inventory->reviewDiscrepancies($itemIdentifiers);

        // Assert
        self::assertCount(1, $reviewedItems);
        self::assertTrue($reviewedItems[0]->isReviewed());
        self::assertSame($articleUuid1->toString(), $reviewedItems[0]->article()->toString());
    }

    public function testReviewDiscrepanciesThrowsWhenNotInReviewStatus(): void
    {
        // Arrange
        $articleUuid = ResourceUuid::generate();
        $zoneStorageUuid = ResourceUuid::generate();
        $inventory = $this->inventoryFactory->createInProgress()->build();
        $inventory->addItem(
            $this->itemFactory->create($articleUuid, $zoneStorageUuid)
                ->withTheoreticalStock(10.0)
                ->withRealStock(8.0)
                ->asCounted()
                ->build()
        );

        $itemIdentifiers = [
            ['articleUuid' => $articleUuid, 'zoneStorageUuid' => $zoneStorageUuid],
        ];

        // Act & Assert
        try {
            $inventory->reviewDiscrepancies($itemIdentifiers);
            self::fail('Expected CannotReviewItemOnNonReviewInventory exception was not thrown');
        } catch (CannotReviewItemOnNonReviewInventory $exception) {
            self::assertSame(CannotReviewItemOnNonReviewInventory::MESSAGE, $exception->getMessage());
            self::assertSame(DomainException::INVALID_ARGUMENT_CODE, $exception->getCode());

            $data = $exception->jsonSerialize();
            self::assertSame(InventoryStatus::IN_PROGRESS->value, $data['currentStatus']);
        }
    }

    public function testReviewDiscrepanciesThrowsWhenItemNotFound(): void
    {
        // Arrange
        $existingArticleUuid = ResourceUuid::generate();
        $unknownArticleUuid = ResourceUuid::generate();
        $zoneStorageUuid = ResourceUuid::generate();

        $inventory = $this->inventoryFactory->createReviewed()->build();
        $inventory->addItem(
            $this->itemFactory->create($existingArticleUuid, $zoneStorageUuid)
                ->withTheoreticalStock(10.0)
                ->withRealStock(8.0)
                ->asCounted()
                ->build()
        );

        $itemIdentifiers = [
            ['articleUuid' => $unknownArticleUuid, 'zoneStorageUuid' => $zoneStorageUuid],
        ];

        // Act & Assert
        try {
            $inventory->reviewDiscrepancies($itemIdentifiers);
            self::fail('Expected ArticleNotFoundInInventory exception was not thrown');
        } catch (ArticleNotFoundInInventory $exception) {
            self::assertSame(ArticleNotFoundInInventory::MESSAGE, $exception->getMessage());
            self::assertSame(DomainException::NOT_FOUND_CODE, $exception->getCode());

            $data = $exception->jsonSerialize();
            self::assertSame($unknownArticleUuid->toString(), $data['articleUuid']);
        }
    }

    public function testReviewDiscrepanciesThrowsWhenItemHasNoDiscrepancy(): void
    {
        // Arrange
        $articleUuid = ResourceUuid::generate();
        $zoneStorageUuid = ResourceUuid::generate();

        $inventory = $this->inventoryFactory->createReviewed()->build();
        // Item WITHOUT discrepancy (realStock == theoreticalStock)
        $inventory->addItem(
            $this->itemFactory->create($articleUuid, $zoneStorageUuid)
                ->withTheoreticalStock(10.0)
                ->withRealStock(10.0)
                ->asCounted()
                ->build()
        );

        $itemIdentifiers = [
            ['articleUuid' => $articleUuid, 'zoneStorageUuid' => $zoneStorageUuid],
        ];

        // Act & Assert
        try {
            $inventory->reviewDiscrepancies($itemIdentifiers);
            self::fail('Expected CannotReviewItemWithoutDiscrepancy exception was not thrown');
        } catch (CannotReviewItemWithoutDiscrepancy $exception) {
            self::assertSame(CannotReviewItemWithoutDiscrepancy::MESSAGE, $exception->getMessage());
            self::assertSame(DomainException::INVALID_ARGUMENT_CODE, $exception->getCode());

            $data = $exception->jsonSerialize();
            self::assertSame($articleUuid->toString(), $data['articleUuid']);
            self::assertSame($zoneStorageUuid->toString(), $data['zoneStorageUuid']);
        }
    }

    public function testReviewDiscrepanciesUpdatesItemsInCollection(): void
    {
        // Arrange
        $articleUuid = ResourceUuid::generate();
        $zoneStorageUuid = ResourceUuid::generate();

        $inventory = $this->inventoryFactory->createReviewed()->build();
        $inventory->addItem(
            $this->itemFactory->create($articleUuid, $zoneStorageUuid)
                ->withTheoreticalStock(10.0)
                ->withRealStock(8.0)
                ->asCounted()
                ->build()
        );

        $itemIdentifiers = [
            ['articleUuid' => $articleUuid, 'zoneStorageUuid' => $zoneStorageUuid],
        ];

        // Act
        $inventory->reviewDiscrepancies($itemIdentifiers);

        // Assert - Check that the item in the collection is now reviewed
        $item = $inventory->items()->findByArticleAndZone($articleUuid, $zoneStorageUuid);
        self::assertNotNull($item);
        self::assertTrue($item->isReviewed());
    }
}
