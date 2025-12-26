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

namespace Inventory\Tests\UseCases\RecordRealStockForZone;

use Inventory\Entities\Exception\ArticleNotFoundInInventory;
use Inventory\Entities\Exception\CannotRecordStockOnNonInProgressInventory;
use Inventory\Entities\Exception\InventoryNotFound;
use Inventory\Entities\ReadModel\ArticleData;
use Inventory\Entities\Repository\InventoryRepository;
use Inventory\Tests\Factory\InventoryFakerFactory;
use Inventory\Tests\Factory\InventoryItemFakerFactory;
use Inventory\UseCases\RecordRealStockForZone\RecordRealStockForZone;
use Inventory\UseCases\RecordRealStockForZone\RecordRealStockForZoneRequest;
use PHPUnit\Framework\TestCase;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\Clock\FrozenClock;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\Quantity;

/**
 * @group unitTest
 *
 * @covers \Inventory\UseCases\RecordRealStockForZone\RecordRealStockForZone
 */
final class RecordRealStockForZoneTest extends TestCase
{
    private InventoryItemFakerFactory $itemFactory;

    protected function setUp(): void
    {
        ClockFactory::initialize(new FrozenClock(new \DateTimeImmutable('2025-12-01')));
        $this->itemFactory = new InventoryItemFakerFactory();
    }

    public function testRecordRealStockSuccessfullyUpdatesItemAndReturnsDifference(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $articleUuid = ResourceUuid::generate();
        $zoneStorageUuid = ResourceUuid::generate();
        $inventory = (new InventoryFakerFactory())->createInProgress()
            ->withUuid($inventoryUuid)
            ->build()
        ;
        $inventory->addItem($this->itemFactory->create($articleUuid, $zoneStorageUuid)->withTheoreticalStock(10.0)->build());

        $repository = $this->createMock(InventoryRepository::class);
        $useCase = new RecordRealStockForZone($repository);
        $request = $this->createMock(RecordRealStockForZoneRequest::class);

        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);
        $request->expects(self::once())
            ->method('articlesData')
            ->willReturn([new ArticleData($articleUuid, realStock: Quantity::fromUnit(12.5))])
        ;
        $request->expects(self::once())->method('zoneStorageUuid')->willReturn($zoneStorageUuid);

        $repository->expects(self::once())
            ->method('getByUuid')
            ->with($inventoryUuid)
            ->willReturn($inventory)
        ;
        $repository->expects(self::once())->method('save')->with($inventory);

        // Act
        $response = $useCase->execute($request);

        // Assert
        $items = $response->inventoryItems;
        self::assertCount(1, $items);

        $firstItem = $items[0];
        self::assertSame(12500, $firstItem->realStock()->toMilliemes());
        self::assertSame(10000, $firstItem->theoreticalStock()->toMilliemes());
        self::assertSame(2500, $firstItem->calculateDifference()->toMilliemes());
        self::assertTrue($firstItem->calculateDifference()->isPositive());
    }

    public function testRecordRealStockThrowsExceptionIfInventoryNotFound(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();

        $repository = $this->createMock(InventoryRepository::class);
        $useCase = new RecordRealStockForZone($repository);
        $request = $this->createMock(RecordRealStockForZoneRequest::class);

        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);

        $repository->expects(self::once())
            ->method('getByUuid')
            ->with($inventoryUuid)
            ->willThrowException(new InventoryNotFound($inventoryUuid))
        ;
        $repository->expects(self::never())->method('save');

        $this->expectException(InventoryNotFound::class);

        // Act
        $useCase->execute($request);
    }

    public function testRecordRealStockThrowsExceptionIfInventoryNotInProgress(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $articleUuid = ResourceUuid::generate();
        $inventory = (new InventoryFakerFactory())->createDraft()
            ->withUuid($inventoryUuid)
            ->build()
        ;

        $repository = $this->createMock(InventoryRepository::class);
        $useCase = new RecordRealStockForZone($repository);
        $request = $this->createMock(RecordRealStockForZoneRequest::class);

        $zoneStorageUuid = ResourceUuid::generate();

        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);
        $request->expects(self::once())
            ->method('articlesData')
            ->willReturn([new ArticleData($articleUuid, realStock: Quantity::fromUnit(10.0))])
        ;
        $request->expects(self::once())->method('zoneStorageUuid')->willReturn($zoneStorageUuid);

        $repository->expects(self::once())
            ->method('getByUuid')
            ->with($inventoryUuid)
            ->willReturn($inventory)
        ;
        $repository->expects(self::never())->method('save');

        $this->expectException(CannotRecordStockOnNonInProgressInventory::class);

        // Act
        $useCase->execute($request);
    }

    public function testRecordRealStockThrowsExceptionIfArticleNotFound(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $existingArticleUuid = ResourceUuid::generate();
        $unknownArticleUuid = ResourceUuid::generate();
        $inventory = (new InventoryFakerFactory())->createInProgress()
            ->withUuid($inventoryUuid)
            ->build()
        ;
        $existingZoneStorageUuid = ResourceUuid::generate();
        $inventory->addItem($this->itemFactory->create($existingArticleUuid, $existingZoneStorageUuid)->build());

        $repository = $this->createMock(InventoryRepository::class);
        $useCase = new RecordRealStockForZone($repository);
        $request = $this->createMock(RecordRealStockForZoneRequest::class);

        $unknownZoneStorageUuid = ResourceUuid::generate();

        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);
        $request->expects(self::once())
            ->method('articlesData')
            ->willReturn([new ArticleData($unknownArticleUuid, realStock: Quantity::fromUnit(10.0))])
        ;
        $request->expects(self::once())->method('zoneStorageUuid')->willReturn($unknownZoneStorageUuid);

        $repository->expects(self::once())
            ->method('getByUuid')
            ->with($inventoryUuid)
            ->willReturn($inventory)
        ;
        $repository->expects(self::never())->method('save');

        $this->expectException(ArticleNotFoundInInventory::class);

        // Act
        $useCase->execute($request);
    }

    public function testRecordRealStockCalculatesNegativeDifference(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $articleUuid = ResourceUuid::generate();
        $zoneStorageUuid = ResourceUuid::generate();
        $inventory = (new InventoryFakerFactory())->createInProgress()
            ->withUuid($inventoryUuid)
            ->build()
        ;
        $inventory->addItem($this->itemFactory->create($articleUuid, $zoneStorageUuid)->withTheoreticalStock(10.0)->build());

        $repository = $this->createMock(InventoryRepository::class);
        $useCase = new RecordRealStockForZone($repository);
        $request = $this->createMock(RecordRealStockForZoneRequest::class);

        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);
        $request->expects(self::once())
            ->method('articlesData')
            ->willReturn([new ArticleData($articleUuid, realStock: Quantity::fromUnit(7.5))])
        ;
        $request->expects(self::once())->method('zoneStorageUuid')->willReturn($zoneStorageUuid);

        $repository->expects(self::once())
            ->method('getByUuid')
            ->with($inventoryUuid)
            ->willReturn($inventory)
        ;
        $repository->expects(self::once())->method('save');

        // Act
        $response = $useCase->execute($request);

        // Assert
        $items = $response->inventoryItems;
        $firstItem = $items[0];

        self::assertSame(-2500, $firstItem->calculateDifference()->toMilliemes());
        self::assertTrue($firstItem->calculateDifference()->isNegative());
    }
}
