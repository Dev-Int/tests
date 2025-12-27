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
}
