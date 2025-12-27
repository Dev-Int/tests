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
use Inventory\Entities\Exception\CannotRecordStockOnNonInProgressInventory;
use Inventory\Entities\ReadModel\ArticleData;
use Inventory\Entities\VO\InventoryStatus;
use Inventory\Tests\Factory\InventoryFakerFactory;
use Inventory\Tests\Factory\InventoryItemFakerFactory;
use PHPUnit\Framework\TestCase;
use Shared\Entities\Exception\DomainException;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\Quantity;

/**
 * @group unitTest
 *
 * @covers \Inventory\Entities\Inventory
 */
final class InventoryRecordRealStockTest extends TestCase
{
    private InventoryItemFakerFactory $itemFactory;

    protected function setUp(): void
    {
        $this->itemFactory = new InventoryItemFakerFactory();
    }

    public function testRecordRealStockSucceedsWhenStatusIsInProgress(): void
    {
        // Arrange
        $articleUuid = ResourceUuid::generate();
        $zoneStorageUuid = ResourceUuid::generate();
        $inventory = (new InventoryFakerFactory())->createInProgress()->build();
        $inventory->addItem($this->itemFactory->create($articleUuid, $zoneStorageUuid)->build());
        $articlesData = [new ArticleData($articleUuid, realStock: Quantity::fromUnit(12.5))];

        // Act
        $inventory->recordRealStocks(
            articlesData: $articlesData,
            zoneStorageUuid: $zoneStorageUuid,
        );

        // Assert
        $items = iterator_to_array($inventory->items());
        self::assertCount(1, $items);
        self::assertSame(12500, $items[0]->realStock()->toMilliemes());
    }

    public function testRecordRealStockThrowsExceptionWhenStatusIsDraft(): void
    {
        // Arrange
        $articleUuid = ResourceUuid::generate();
        $zoneStorageUuid = ResourceUuid::generate();
        $inventory = (new InventoryFakerFactory())->createDraft()->build();
        $inventory->addItem($this->itemFactory->create($articleUuid, $zoneStorageUuid)->build());
        $articlesData = [new ArticleData($articleUuid, realStock: Quantity::fromUnit(10.0))];

        // Act & Assert
        try {
            $inventory->recordRealStocks($articlesData, $zoneStorageUuid);
            self::fail('Expected CannotRecordStockOnNonInProgressInventory exception was not thrown');
        } catch (CannotRecordStockOnNonInProgressInventory $exception) {
            self::assertSame(CannotRecordStockOnNonInProgressInventory::MESSAGE, $exception->getMessage());
            self::assertSame(DomainException::INVALID_ARGUMENT_CODE, $exception->getCode());

            $data = $exception->jsonSerialize();
            self::assertSame(InventoryStatus::DRAFT->value, $data['currentStatus']);
        }
    }

    public function testRecordRealStockThrowsExceptionWhenStatusIsReview(): void
    {
        // Arrange
        $articleUuid = ResourceUuid::generate();
        $zoneStorageUuid = ResourceUuid::generate();
        $inventory = (new InventoryFakerFactory())->createReviewed()->build();
        $inventory->addItem($this->itemFactory->create($articleUuid, $zoneStorageUuid)->build());
        $articlesData = [new ArticleData($articleUuid, realStock: Quantity::fromUnit(10.0))];

        // Act & Assert
        $this->expectException(CannotRecordStockOnNonInProgressInventory::class);
        $inventory->recordRealStocks($articlesData, $zoneStorageUuid);
    }

    public function testRecordRealStockThrowsExceptionWhenStatusIsCompleted(): void
    {
        // Arrange
        $articleUuid = ResourceUuid::generate();
        $zoneStorageUuid = ResourceUuid::generate();
        $inventory = (new InventoryFakerFactory())->createCompleted()->build();
        $inventory->addItem($this->itemFactory->create($articleUuid, $zoneStorageUuid)->build());
        $articlesData = [new ArticleData($articleUuid, realStock: Quantity::fromUnit(10.0))];

        // Act & Assert
        $this->expectException(CannotRecordStockOnNonInProgressInventory::class);
        $inventory->recordRealStocks($articlesData, $zoneStorageUuid);
    }

    public function testRecordRealStockThrowsExceptionWhenArticleNotFound(): void
    {
        // Arrange
        $existingArticleUuid = ResourceUuid::generate();
        $existingZoneStorageUuid = ResourceUuid::generate();
        $unknownArticleUuid = ResourceUuid::generate();
        $unknownZoneStorageUuid = ResourceUuid::generate();
        $inventory = (new InventoryFakerFactory())->createInProgress()->build();
        $inventory->addItem($this->itemFactory->create($existingArticleUuid, $existingZoneStorageUuid)->build());
        $articlesData = [new ArticleData($unknownArticleUuid, realStock: Quantity::fromUnit(10.0))];

        // Act & Assert
        try {
            $inventory->recordRealStocks($articlesData, $unknownZoneStorageUuid);
            self::fail('Expected ArticleNotFoundInInventory exception was not thrown');
        } catch (ArticleNotFoundInInventory $exception) {
            self::assertSame(ArticleNotFoundInInventory::MESSAGE, $exception->getMessage());
            self::assertSame(DomainException::NOT_FOUND_CODE, $exception->getCode());

            $data = $exception->jsonSerialize();
            self::assertSame($unknownArticleUuid->toString(), $data['articleUuid']);
        }
    }

    public function testRecordRealStockPreservesOtherItemProperties(): void
    {
        // Arrange
        $articleUuid = ResourceUuid::generate();
        $zoneStorageUuid = ResourceUuid::generate();
        $inventory = (new InventoryFakerFactory())->createInProgress()->build();
        $inventory->addItem($this->itemFactory->create($articleUuid, $zoneStorageUuid)->build());
        $articlesData = [new ArticleData($articleUuid, realStock: Quantity::fromUnit(8.0))];

        // Act
        $inventory->recordRealStocks($articlesData, $zoneStorageUuid);

        // Assert
        $items = iterator_to_array($inventory->items());
        self::assertSame(8000, $items[0]->realStock()->toMilliemes());
        self::assertSame(10000, $items[0]->theoreticalStock()->toMilliemes());
        self::assertSame(1500, $items[0]->price()->toInt());
        self::assertSame($articleUuid->toString(), $items[0]->article()->toString());
    }
}
