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

use Inventory\Tests\Factory\InventoryItemFakerFactory;
use PHPUnit\Framework\TestCase;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\Clock\FrozenClock;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\Quantity;

/**
 * @group unitTest
 *
 * @covers \Inventory\Entities\InventoryItem
 */
final class InventoryItemTest extends TestCase
{
    private InventoryItemFakerFactory $itemFactory;

    protected function setUp(): void
    {
        $this->itemFactory = new InventoryItemFakerFactory();
    }

    public function testWithRealStockReturnsNewInstanceWithUpdatedRealStock(): void
    {
        // Arrange
        $item = $this->itemFactory->create()
            ->withTheoreticalStock(10.0)
            ->withRealStock(0.0)
            ->build()
        ;
        $newRealStock = Quantity::fromUnit(12.5);

        // Act
        $updatedItem = $item->withRealStock($newRealStock);

        // Assert
        self::assertNotSame($item, $updatedItem);
        self::assertSame(12500, $updatedItem->realStock()->toMilliemes());
        self::assertSame($item->article()->toString(), $updatedItem->article()->toString());
        self::assertSame($item->theoreticalStock()->toMilliemes(), $updatedItem->theoreticalStock()->toMilliemes());
        self::assertSame($item->price()->toInt(), $updatedItem->price()->toInt());
    }

    public function testWithRealStockPreservesOriginalInstance(): void
    {
        // Arrange
        $item = $this->itemFactory->create()->withRealStock(0.0)->build();

        // Act
        $item->withRealStock(Quantity::fromUnit(15.0));

        // Assert
        self::assertSame(0, $item->realStock()->toMilliemes());
    }

    public function testCalculateDifferenceReturnsPositiveForSurplus(): void
    {
        // Arrange
        $item = $this->itemFactory->create()
            ->withTheoreticalStock(10.0)
            ->withRealStock(12.0)
            ->build()
        ;

        // Act
        $difference = $item->calculateDifference();

        // Assert
        self::assertSame(2000, $difference->toMilliemes());
        self::assertTrue($difference->isPositive());
    }

    public function testCalculateDifferenceReturnsNegativeForShortage(): void
    {
        // Arrange
        $item = $this->itemFactory->create()
            ->withTheoreticalStock(10.0)
            ->withRealStock(8.0)
            ->build()
        ;

        // Act
        $difference = $item->calculateDifference();

        // Assert
        self::assertSame(-2000, $difference->toMilliemes());
        self::assertTrue($difference->isNegative());
    }

    public function testCalculateDifferenceReturnsZeroWhenEqual(): void
    {
        // Arrange
        $item = $this->itemFactory->create()
            ->withTheoreticalStock(10.0)
            ->withRealStock(10.0)
            ->build()
        ;

        // Act
        $difference = $item->calculateDifference();

        // Assert
        self::assertSame(0, $difference->toMilliemes());
        self::assertTrue($difference->isZero());
    }

    public function testItemHasZoneStorage(): void
    {
        // Arrange
        $articleUuid = ResourceUuid::generate();
        $zoneStorageUuid = ResourceUuid::generate();

        // Act
        $item = $this->itemFactory->create($articleUuid, $zoneStorageUuid)->build();

        // Assert
        self::assertSame($zoneStorageUuid->toString(), $item->zoneStorage()->toString());
        self::assertSame($articleUuid->toString(), $item->article()->toString());
    }

    public function testWithRealStockPreservesZoneStorage(): void
    {
        // Arrange
        $zoneStorageUuid = ResourceUuid::generate();
        $item = $this->itemFactory->create(zoneStorage: $zoneStorageUuid)->build();
        $newRealStock = Quantity::fromUnit(15.0);

        // Act
        $updatedItem = $item->withRealStock($newRealStock);

        // Assert
        self::assertSame($zoneStorageUuid->toString(), $updatedItem->zoneStorage()->toString());
    }

    public function testNewItemHasNotBeenCounted(): void
    {
        // Arrange & Act
        $item = $this->itemFactory->create()->build();

        // Assert
        self::assertFalse($item->hasBeenCounted());
        self::assertNull($item->countedAt());
    }

    public function testWithRealStockSetsCountedAt(): void
    {
        // Arrange
        $frozenTime = new \DateTimeImmutable('2025-12-15 10:30:00');
        ClockFactory::initialize(new FrozenClock($frozenTime));

        $item = $this->itemFactory->create()->build();

        // Act
        $countedItem = $item->withRealStock(Quantity::fromUnit(5.0));

        // Assert
        self::assertTrue($countedItem->hasBeenCounted());
        self::assertNotNull($countedItem->countedAt());
        self::assertEquals($frozenTime, $countedItem->countedAt());
    }

    public function testWithRealStockToZeroStillMarksAsCounted(): void
    {
        // Arrange
        $frozenTime = new \DateTimeImmutable('2025-12-15 10:30:00');
        ClockFactory::initialize(new FrozenClock($frozenTime));

        $item = $this->itemFactory->create()->build();

        // Act - Even setting to 0 should mark as counted
        $countedItem = $item->withRealStock(Quantity::fromUnit(0.0));

        // Assert
        self::assertTrue($countedItem->hasBeenCounted());
        self::assertEquals($frozenTime, $countedItem->countedAt());
    }

    public function testNewItemIsNotReviewedByDefault(): void
    {
        // Arrange & Act
        $item = $this->itemFactory->create()->build();

        // Assert
        self::assertFalse($item->isReviewed());
    }

    public function testWithReviewedTrueReturnsNewInstanceMarkedAsReviewed(): void
    {
        // Arrange
        $item = $this->itemFactory->create()->build();

        // Act
        $reviewedItem = $item->withReviewed(true);

        // Assert
        self::assertNotSame($item, $reviewedItem);
        self::assertTrue($reviewedItem->isReviewed());
        self::assertFalse($item->isReviewed());
    }

    public function testWithReviewedPreservesOtherProperties(): void
    {
        // Arrange
        $item = $this->itemFactory->create()
            ->withTheoreticalStock(10.0)
            ->withRealStock(8.0)
            ->asCounted()
            ->build()
        ;

        // Act
        $reviewedItem = $item->withReviewed(true);

        // Assert
        self::assertSame($item->article()->toString(), $reviewedItem->article()->toString());
        self::assertSame($item->zoneStorage()->toString(), $reviewedItem->zoneStorage()->toString());
        self::assertSame($item->theoreticalStock()->toMilliemes(), $reviewedItem->theoreticalStock()->toMilliemes());
        self::assertSame($item->realStock()->toMilliemes(), $reviewedItem->realStock()->toMilliemes());
        self::assertEquals($item->countedAt(), $reviewedItem->countedAt());
    }

    public function testHasDiscrepancyReturnsTrueWhenRealDiffersFromTheoretical(): void
    {
        // Arrange
        $item = $this->itemFactory->create()
            ->withTheoreticalStock(10.0)
            ->withRealStock(8.0)
            ->build()
        ;

        // Act & Assert
        self::assertTrue($item->hasDiscrepancy());
    }

    public function testHasDiscrepancyReturnsFalseWhenStocksAreEqual(): void
    {
        // Arrange
        $item = $this->itemFactory->create()
            ->withTheoreticalStock(10.0)
            ->withRealStock(10.0)
            ->build()
        ;

        // Act & Assert
        self::assertFalse($item->hasDiscrepancy());
    }

    public function testHasDiscrepancyReturnsTrueForPositiveDifference(): void
    {
        // Arrange
        $item = $this->itemFactory->create()
            ->withTheoreticalStock(10.0)
            ->withRealStock(12.0)
            ->build()
        ;

        // Act & Assert
        self::assertTrue($item->hasDiscrepancy());
    }
}
