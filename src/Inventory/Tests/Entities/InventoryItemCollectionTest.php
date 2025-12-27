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

use Inventory\Entities\InventoryItemCollection;
use Inventory\Tests\Factory\InventoryItemFakerFactory;
use PHPUnit\Framework\TestCase;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\Quantity;

/**
 * @group unitTest
 *
 * @covers \Inventory\Entities\InventoryItemCollection
 */
final class InventoryItemCollectionTest extends TestCase
{
    private InventoryItemFakerFactory $itemFactory;

    protected function setUp(): void
    {
        $this->itemFactory = new InventoryItemFakerFactory();
    }

    public function testFindByArticleAndZoneReturnsItemWhenExists(): void
    {
        // Arrange
        $articleUuid = ResourceUuid::generate();
        $zoneStorageUuid = ResourceUuid::generate();
        $item = $this->itemFactory->create($articleUuid, $zoneStorageUuid)->build();

        $collection = new InventoryItemCollection();
        $collection->add($item);

        // Act
        $found = $collection->findByArticleAndZone($articleUuid, $zoneStorageUuid);

        // Assert
        self::assertNotNull($found);
        self::assertSame($articleUuid->toString(), $found->article()->toString());
        self::assertSame($zoneStorageUuid->toString(), $found->zoneStorage()->toString());
    }

    public function testFindByArticleAndZoneReturnsNullWhenArticleNotFound(): void
    {
        // Arrange
        $collection = new InventoryItemCollection();
        $collection->add($this->itemFactory->create()->build());

        // Act
        $found = $collection->findByArticleAndZone(
            ResourceUuid::generate(),
            ResourceUuid::generate()
        );

        // Assert
        self::assertNull($found);
    }

    public function testFindByArticleAndZoneReturnsNullWhenZoneNotMatching(): void
    {
        // Arrange
        $articleUuid = ResourceUuid::generate();
        $zoneA = ResourceUuid::generate();
        $zoneB = ResourceUuid::generate();

        $collection = new InventoryItemCollection();
        $collection->add($this->itemFactory->create($articleUuid, $zoneA)->build());

        // Act
        $found = $collection->findByArticleAndZone($articleUuid, $zoneB);

        // Assert
        self::assertNull($found);
    }

    public function testFindByArticleAndZoneWithSameArticleInMultipleZones(): void
    {
        // Arrange
        $articleUuid = ResourceUuid::generate();
        $zoneA = ResourceUuid::generate();
        $zoneB = ResourceUuid::generate();

        $itemInZoneA = $this->itemFactory->create($articleUuid, $zoneA)->withTheoreticalStock(5.0)->build();
        $itemInZoneB = $this->itemFactory->create($articleUuid, $zoneB)->withTheoreticalStock(10.0)->build();

        $collection = new InventoryItemCollection();
        $collection->add($itemInZoneA);
        $collection->add($itemInZoneB);

        // Act
        $foundA = $collection->findByArticleAndZone($articleUuid, $zoneA);
        $foundB = $collection->findByArticleAndZone($articleUuid, $zoneB);

        // Assert
        self::assertNotNull($foundA);
        self::assertSame(5000, $foundA->theoreticalStock()->toMilliemes());
        self::assertNotNull($foundB);
        self::assertSame(10000, $foundB->theoreticalStock()->toMilliemes());
    }

    public function testReplaceUpdatesCorrectItem(): void
    {
        // Arrange
        $articleUuid = ResourceUuid::generate();
        $zoneStorageUuid = ResourceUuid::generate();
        $originalItem = $this->itemFactory->create($articleUuid, $zoneStorageUuid)->withTheoreticalStock(5.0)->build();
        $updatedItems = [$originalItem->withRealStock(Quantity::fromUnit(7.0))];

        $collection = new InventoryItemCollection();
        $collection->add($originalItem);

        // Act
        $collection->replace($updatedItems);

        // Assert
        $found = $collection->findByArticleAndZone($articleUuid, $zoneStorageUuid);
        self::assertNotNull($found);
        self::assertSame(7000, $found->realStock()->toMilliemes());
    }

    public function testReplaceOnlyUpdatesMatchingItem(): void
    {
        // Arrange
        $articleUuid = ResourceUuid::generate();
        $zoneA = ResourceUuid::generate();
        $zoneB = ResourceUuid::generate();

        $itemInZoneA = $this->itemFactory->create($articleUuid, $zoneA)->build();
        $itemInZoneB = $this->itemFactory->create($articleUuid, $zoneB)->build();

        $collection = new InventoryItemCollection();
        $collection->add($itemInZoneA);
        $collection->add($itemInZoneB);

        // Act
        $updatedItems = [$itemInZoneA->withRealStock(Quantity::fromUnit(99.0))];
        $collection->replace($updatedItems);

        // Assert
        $foundA = $collection->findByArticleAndZone($articleUuid, $zoneA);
        $foundB = $collection->findByArticleAndZone($articleUuid, $zoneB);

        self::assertSame(99000, $foundA?->realStock()->toMilliemes());
        self::assertSame(0, $foundB?->realStock()->toMilliemes()); // Original value
    }

    public function testGetTotalRealStockForArticleSumsAcrossZones(): void
    {
        // Arrange
        $articleUuid = ResourceUuid::generate();
        $zoneA = ResourceUuid::generate();
        $zoneB = ResourceUuid::generate();
        $zoneC = ResourceUuid::generate();

        $collection = new InventoryItemCollection();
        $collection->add($this->itemFactory->createWithRealStock(5.0, $articleUuid, $zoneA)->build());
        $collection->add($this->itemFactory->createWithRealStock(10.0, $articleUuid, $zoneB)->build());
        $collection->add($this->itemFactory->createWithRealStock(3.5, $articleUuid, $zoneC)->build());

        // Act
        $total = $collection->getTotalRealStockForArticle($articleUuid);

        // Assert
        self::assertSame(18500, $total->toMilliemes(), '5.0 + 10.0 + 3.5 = 18.5 units = 18500 milliemes');
    }

    public function testGetTotalRealStockForArticleReturnsZeroWhenArticleNotFound(): void
    {
        // Arrange
        $collection = new InventoryItemCollection();
        $collection->add($this->itemFactory->createWithRealStock(10.0)->build());

        // Act
        $total = $collection->getTotalRealStockForArticle(ResourceUuid::generate());

        // Assert
        self::assertSame(0, $total->toMilliemes());
    }

    public function testGetTotalRealStockForArticleWithSingleZone(): void
    {
        // Arrange
        $articleUuid = ResourceUuid::generate();
        $collection = new InventoryItemCollection();
        $collection->add($this->itemFactory->createWithRealStock(7.5, $articleUuid)->build());

        // Act
        $total = $collection->getTotalRealStockForArticle($articleUuid);

        // Assert
        self::assertSame(7500, $total->toMilliemes());
    }

    public function testCountReturnsActualNumberOfItems(): void
    {
        // Arrange
        $collection = new InventoryItemCollection();
        $collection->add($this->itemFactory->create()->build());
        $collection->add($this->itemFactory->create()->build());
        $collection->add($this->itemFactory->create()->build());

        // Act & Assert
        self::assertCount(3, $collection);
    }

    public function testFilterByZoneReturnsOnlyMatchingItems(): void
    {
        // Arrange
        $zoneA = ResourceUuid::generate();
        $zoneB = ResourceUuid::generate();

        $collection = new InventoryItemCollection();
        $collection->add($this->itemFactory->create(zoneStorage: $zoneA)->build());
        $collection->add($this->itemFactory->create(zoneStorage: $zoneA)->build());
        $collection->add($this->itemFactory->create(zoneStorage: $zoneB)->build());

        // Act
        $itemsInZoneA = $collection->filterByZone($zoneA);

        // Assert
        self::assertCount(2, $itemsInZoneA);
        foreach ($itemsInZoneA as $item) {
            self::assertSame($zoneA->toString(), $item->zoneStorage()->toString());
        }
    }

    public function testFilterByZoneReturnsEmptyArrayWhenNoMatch(): void
    {
        // Arrange
        $collection = new InventoryItemCollection();
        $collection->add($this->itemFactory->create()->build());

        // Act
        $items = $collection->filterByZone(ResourceUuid::generate());

        // Assert
        self::assertCount(0, $items);
    }

    public function testGetArticleUuidsReturnsUniqueArticles(): void
    {
        // Arrange
        $articleA = ResourceUuid::generate();
        $articleB = ResourceUuid::generate();
        $zoneA = ResourceUuid::generate();
        $zoneB = ResourceUuid::generate();

        $collection = new InventoryItemCollection();
        $collection->add($this->itemFactory->create($articleA, $zoneA)->build());
        $collection->add($this->itemFactory->create($articleA, $zoneB)->build()); // Same article, diff zone
        $collection->add($this->itemFactory->create($articleB, $zoneA)->build());

        // Act
        $articleUuids = $collection->getArticleUuids();

        // Assert
        self::assertCount(2, $articleUuids);
    }

    public function testGetArticleUuidsReturnsEmptyArrayWhenCollectionEmpty(): void
    {
        // Arrange
        $collection = new InventoryItemCollection();

        // Act
        $articleUuids = $collection->getArticleUuids();

        // Assert
        self::assertCount(0, $articleUuids);
    }
}
