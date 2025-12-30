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

namespace Inventory\Tests\UseCases\CancelInventory;

use Inventory\Entities\Exception\CannotCancelCompletedInventory;
use Inventory\Entities\Exception\InventoryNotFound;
use Inventory\Entities\Repository\InventoryRepository;
use Inventory\Entities\VO\InventoryStatus;
use Inventory\Tests\Factory\InventoryFakerFactory;
use Inventory\Tests\Factory\InventoryItemFakerFactory;
use Inventory\UseCases\CancelInventory\CancelInventory;
use Inventory\UseCases\CancelInventory\CancelInventoryRequest;
use PHPUnit\Framework\TestCase;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\Clock\FrozenClock;
use Shared\Entities\ResourceUuid;

/**
 * @group unitTest
 *
 * @covers \Inventory\UseCases\CancelInventory\CancelInventory
 */
final class CancelInventoryTest extends TestCase
{
    private InventoryItemFakerFactory $itemFactory;

    protected function setUp(): void
    {
        ClockFactory::initialize(clock: new FrozenClock(now: new \DateTimeImmutable('2025-12-01')));
        $this->itemFactory = new InventoryItemFakerFactory();
    }

    public function testCancelDraftInventorySuccessfully(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $inventory = (new InventoryFakerFactory())->createDraft()
            ->withUuid($inventoryUuid)
            ->build()
        ;

        $repository = $this->createMock(InventoryRepository::class);
        $useCase = new CancelInventory(inventoryRepository: $repository);
        $request = $this->createMock(CancelInventoryRequest::class);

        // Assert setup
        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);

        $repository->expects(self::once())->method('getByUuid')->with($inventoryUuid)->willReturn($inventory);
        $repository->expects(self::once())->method('save')->with($inventory);

        // Act
        $response = $useCase->execute($request);

        // Assert
        self::assertTrue($response->inventory->status()->equals(InventoryStatus::CANCELLED));
    }

    public function testCancelInProgressInventorySuccessfully(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $inventory = (new InventoryFakerFactory())->createInProgress()
            ->withUuid($inventoryUuid)
            ->build()
        ;

        // Add some items
        $item = $this->itemFactory->create()->build();
        $inventory->addItem($item);

        $repository = $this->createMock(InventoryRepository::class);
        $useCase = new CancelInventory(inventoryRepository: $repository);
        $request = $this->createMock(CancelInventoryRequest::class);

        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);

        $repository->expects(self::once())->method('getByUuid')->with($inventoryUuid)->willReturn($inventory);
        $repository->expects(self::once())->method('save')->with($inventory);

        // Act
        $response = $useCase->execute($request);

        // Assert
        self::assertTrue($response->inventory->status()->equals(InventoryStatus::CANCELLED));
    }

    public function testCancelInventoryThrowsExceptionWhenInventoryNotFound(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();

        $repository = $this->createMock(InventoryRepository::class);
        $useCase = new CancelInventory(inventoryRepository: $repository);
        $request = $this->createMock(CancelInventoryRequest::class);

        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);

        $repository->expects(self::once())
            ->method('getByUuid')
            ->with($inventoryUuid)
            ->willThrowException(new InventoryNotFound(inventoryUuid: $inventoryUuid))
        ;
        $repository->expects(self::never())->method('save');

        $this->expectException(InventoryNotFound::class);

        // Act
        $useCase->execute($request);
    }

    public function testCancelInventoryThrowsExceptionWhenAlreadyCompleted(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $inventory = (new InventoryFakerFactory())->createCompleted()
            ->withUuid($inventoryUuid)
            ->build()
        ;

        $repository = $this->createMock(InventoryRepository::class);
        $useCase = new CancelInventory(inventoryRepository: $repository);
        $request = $this->createMock(CancelInventoryRequest::class);

        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);

        $repository->expects(self::once())->method('getByUuid')->with($inventoryUuid)->willReturn($inventory);
        $repository->expects(self::never())->method('save');

        $this->expectException(CannotCancelCompletedInventory::class);

        // Act
        $useCase->execute($request);
    }

    public function testCancelInventoryThrowsExceptionWhenAlreadyCancelled(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $inventory = (new InventoryFakerFactory())->createCancelled()
            ->withUuid($inventoryUuid)
            ->build()
        ;

        $repository = $this->createMock(InventoryRepository::class);
        $useCase = new CancelInventory(inventoryRepository: $repository);
        $request = $this->createMock(CancelInventoryRequest::class);

        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);

        $repository->expects(self::once())->method('getByUuid')->with($inventoryUuid)->willReturn($inventory);
        $repository->expects(self::never())->method('save');

        $this->expectException(CannotCancelCompletedInventory::class);

        // Act
        $useCase->execute($request);
    }

    /**
     * These test documents that CancelInventory does NOT depend on ArticleStockUpdater.
     * Unlike CompleteInventory, cancelling an inventory should NOT update article stocks.
     */
    public function testCancelInventoryDoesNotRequireArticleStockUpdater(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $inventory = (new InventoryFakerFactory())->createInProgress()
            ->withUuid($inventoryUuid)
            ->build()
        ;

        // Add items with stocks (these should NOT be propagated to articles)
        $item = $this->itemFactory
            ->createWithPreciseStocks(theoreticalStock: 10.0, realStock: 8.0)
            ->build()
        ;
        $inventory->addItem($item);

        $repository = $this->createMock(InventoryRepository::class);
        // NOTE: CancelInventory constructor does NOT take ArticleStockUpdaterInterface
        $useCase = new CancelInventory(inventoryRepository: $repository);
        $request = $this->createMock(CancelInventoryRequest::class);

        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);

        $repository->expects(self::once())->method('getByUuid')->with($inventoryUuid)->willReturn($inventory);
        $repository->expects(self::once())->method('save')->with($inventory);

        // Act
        $response = $useCase->execute($request);

        // Assert - inventory cancelled, no stock updates occurred
        self::assertTrue($response->inventory->status()->equals(InventoryStatus::CANCELLED));
    }

    public function testCancelInventoryPreservesItems(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $inventory = (new InventoryFakerFactory())->createInProgress()
            ->withUuid($inventoryUuid)
            ->build()
        ;

        // Add items
        $item1 = $this->itemFactory->create()->build();
        $item2 = $this->itemFactory->create()->build();
        $inventory->addItem($item1);
        $inventory->addItem($item2);

        $repository = $this->createMock(InventoryRepository::class);
        $useCase = new CancelInventory(inventoryRepository: $repository);
        $request = $this->createMock(CancelInventoryRequest::class);

        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);

        $repository->expects(self::once())->method('getByUuid')->with($inventoryUuid)->willReturn($inventory);
        $repository->expects(self::once())->method('save')->with($inventory);

        // Act
        $response = $useCase->execute($request);

        // Assert - items should remain (not cleared)
        self::assertCount(2, $response->inventory->items());
    }

    public function testCancelInventoryReturnsCorrectResponse(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $inventory = (new InventoryFakerFactory())->createDraft()
            ->withUuid($inventoryUuid)
            ->build()
        ;

        $repository = $this->createMock(InventoryRepository::class);
        $useCase = new CancelInventory(inventoryRepository: $repository);
        $request = $this->createMock(CancelInventoryRequest::class);

        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);

        $repository->expects(self::once())->method('getByUuid')->with($inventoryUuid)->willReturn($inventory);
        $repository->expects(self::once())->method('save')->with($inventory);

        // Act
        $response = $useCase->execute($request);

        // Assert
        self::assertSame($inventory, $response->inventory);
        self::assertTrue($response->inventory->status()->isCancelled());
    }
}
