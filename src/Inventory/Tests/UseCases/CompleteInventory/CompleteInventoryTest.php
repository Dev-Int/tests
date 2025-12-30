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

namespace Inventory\Tests\UseCases\CompleteInventory;

use Inventory\Entities\Exception\InvalidStatusTransition;
use Inventory\Entities\Exception\InventoryNotFound;
use Inventory\Entities\Exception\UnreviewedDiscrepancies;
use Inventory\Entities\Repository\InventoryRepository;
use Inventory\Entities\VO\InventoryStatus;
use Inventory\Tests\Factory\InventoryFakerFactory;
use Inventory\Tests\Factory\InventoryItemFakerFactory;
use Inventory\UseCases\CompleteInventory\CompleteInventory;
use Inventory\UseCases\CompleteInventory\CompleteInventoryRequest;
use Inventory\UseCases\Gateway\ArticleStockUpdaterInterface;
use Inventory\UseCases\Gateway\StockUpdateCommand;
use PHPUnit\Framework\TestCase;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\Clock\FrozenClock;
use Shared\Entities\Persistence\TransactionalExecutorInterface;
use Shared\Entities\ResourceUuid;

/**
 * @group unitTest
 *
 * @covers \Inventory\UseCases\CompleteInventory\CompleteInventory
 */
final class CompleteInventoryTest extends TestCase
{
    private InventoryItemFakerFactory $itemFactory;
    private TransactionalExecutorInterface $transactionalExecutor;

    protected function setUp(): void
    {
        ClockFactory::initialize(clock: new FrozenClock(now: new \DateTimeImmutable('2025-12-01')));
        $this->itemFactory = new InventoryItemFakerFactory();
        $this->transactionalExecutor = $this->createPassthroughTransactionalExecutor();
    }

    public function testCompleteInventorySuccessfully(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();

        $inventory = (new InventoryFakerFactory())->createReviewed()
            ->withUuid($inventoryUuid)
            ->build()
        ;

        // Add reviewed item with discrepancy
        $reviewedItem = $this->itemFactory
            ->createWithPreciseStocks(theoreticalStock: 10.0, realStock: 8.0)
            ->asReviewed()
            ->build()
        ;
        $inventory->addItem($reviewedItem);

        $repository = $this->createMock(InventoryRepository::class);
        $stockUpdater = $this->createMock(ArticleStockUpdaterInterface::class);
        $useCase = new CompleteInventory(
            inventoryRepository: $repository,
            articleStockUpdater: $stockUpdater,
            transactionalExecutor: $this->transactionalExecutor,
        );
        $request = $this->createMock(CompleteInventoryRequest::class);

        // Assert setup
        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);
        $repository->expects(self::once())->method('getByUuid')->willReturn($inventory);
        $repository->expects(self::once())->method('save');
        $stockUpdater->expects(self::once())
            ->method('updateStocks')
            ->with(self::callback(static function (array $updates): bool {
                return \count($updates) === 1
                    && $updates[0] instanceof StockUpdateCommand
                    && $updates[0]->newQuantityMilliemes === 8000;
            }))
        ;

        // Act
        $response = $useCase->execute($request);

        // Assert
        self::assertTrue($response->inventory->status()->equals(InventoryStatus::COMPLETED));
        self::assertSame(1, $response->articlesUpdated);
        self::assertSame(-2000, $response->discrepancyAmount->toInt()); // (8.0 - 10.0) × 10.00€ = -20.00€ (shortage)
    }

    public function testCompleteInventoryThrowsExceptionWhenInventoryNotFound(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();

        $repository = $this->createMock(InventoryRepository::class);
        $stockUpdater = $this->createMock(ArticleStockUpdaterInterface::class);
        $useCase = new CompleteInventory(
            inventoryRepository: $repository,
            articleStockUpdater: $stockUpdater,
            transactionalExecutor: $this->transactionalExecutor,
        );
        $request = $this->createMock(CompleteInventoryRequest::class);

        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);
        $repository->expects(self::once())
            ->method('getByUuid')
            ->willThrowException(new InventoryNotFound(inventoryUuid: $inventoryUuid))
        ;
        $repository->expects(self::never())->method('save');
        $stockUpdater->expects(self::never())->method('updateStocks');

        $this->expectException(InventoryNotFound::class);

        // Act
        $useCase->execute($request);
    }

    public function testCompleteInventoryThrowsExceptionWhenNotInReview(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $inventory = (new InventoryFakerFactory())->createInProgress()
            ->withUuid($inventoryUuid)
            ->build()
        ;

        $repository = $this->createMock(InventoryRepository::class);
        $stockUpdater = $this->createMock(ArticleStockUpdaterInterface::class);
        $useCase = new CompleteInventory(
            inventoryRepository: $repository,
            articleStockUpdater: $stockUpdater,
            transactionalExecutor: $this->transactionalExecutor,
        );
        $request = $this->createMock(CompleteInventoryRequest::class);

        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);
        $repository->expects(self::once())->method('getByUuid')->willReturn($inventory);
        $repository->expects(self::never())->method('save');
        $stockUpdater->expects(self::never())->method('updateStocks');

        $this->expectException(InvalidStatusTransition::class);

        // Act
        $useCase->execute($request);
    }

    public function testCompleteInventoryThrowsExceptionWhenUnreviewedDiscrepancies(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $inventory = (new InventoryFakerFactory())->createReviewed()
            ->withUuid($inventoryUuid)
            ->build()
        ;

        // Add unreviewed item with discrepancy
        $unreviewedItem = $this->itemFactory
            ->createWithPreciseStocks(theoreticalStock: 10.0, realStock: 8.0)
            ->build() // NOT asReviewed()
        ;
        $inventory->addItem($unreviewedItem);

        $repository = $this->createMock(InventoryRepository::class);
        $stockUpdater = $this->createMock(ArticleStockUpdaterInterface::class);
        $useCase = new CompleteInventory(
            inventoryRepository: $repository,
            articleStockUpdater: $stockUpdater,
            transactionalExecutor: $this->transactionalExecutor,
        );
        $request = $this->createMock(CompleteInventoryRequest::class);

        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);
        $repository->expects(self::once())->method('getByUuid')->willReturn($inventory);
        $repository->expects(self::never())->method('save');
        $stockUpdater->expects(self::never())->method('updateStocks');

        $this->expectException(UnreviewedDiscrepancies::class);

        // Act
        $useCase->execute($request);
    }

    public function testCompleteInventoryAggregatesStocksAcrossZones(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $articleUuid = ResourceUuid::generate();
        $zone1 = ResourceUuid::generate();
        $zone2 = ResourceUuid::generate();

        $inventory = (new InventoryFakerFactory())->createReviewed()
            ->withUuid($inventoryUuid)
            ->build()
        ;

        // Same article in two different zones
        $itemZone1 = $this->itemFactory
            ->create(article: $articleUuid, zoneStorage: $zone1)
            ->withPrice(1000)
            ->withTheoreticalStock(10.0)
            ->withRealStock(5.0)
            ->asCounted()
            ->asReviewed()
            ->build()
        ;
        $itemZone2 = $this->itemFactory
            ->create(article: $articleUuid, zoneStorage: $zone2)
            ->withPrice(1000)
            ->withTheoreticalStock(5.0)
            ->withRealStock(3.0)
            ->asCounted()
            ->asReviewed()
            ->build()
        ;
        $inventory->addItem($itemZone1);
        $inventory->addItem($itemZone2);

        $repository = $this->createMock(InventoryRepository::class);
        $stockUpdater = $this->createMock(ArticleStockUpdaterInterface::class);
        $useCase = new CompleteInventory(
            inventoryRepository: $repository,
            articleStockUpdater: $stockUpdater,
            transactionalExecutor: $this->transactionalExecutor,
        );
        $request = $this->createMock(CompleteInventoryRequest::class);

        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);
        $repository->expects(self::once())->method('getByUuid')->willReturn($inventory);
        $repository->expects(self::once())->method('save');
        $stockUpdater->expects(self::once())
            ->method('updateStocks')
            ->with(self::callback(static function (array $updates) use ($articleUuid): bool {
                // Should have only 1 update (aggregated)
                if (\count($updates) !== 1) {
                    return false;
                }

                /** @var StockUpdateCommand $update */
                $update = $updates[0];

                // Total realStock: 5.0 + 3.0 = 8.0 (8000 milliemes)
                return $update->articleUuid->toString() === $articleUuid->toString()
                    && $update->newQuantityMilliemes === 8000;
            }))
        ;

        // Act
        $response = $useCase->execute($request);

        // Assert
        self::assertSame(1, $response->articlesUpdated);
    }

    public function testCompleteInventoryCalculatesDiscrepancyAmountCorrectly(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();

        $inventory = (new InventoryFakerFactory())->createReviewed()
            ->withUuid($inventoryUuid)
            ->build()
        ;

        // Item 1: (8 - 10) × 10.00€ = -2 × 10.00€ = -20.00€ (shortage)
        $item1 = $this->itemFactory
            ->create()
            ->withPrice(1000) // 10.00€
            ->withTheoreticalStock(10.0)
            ->withRealStock(8.0)
            ->asCounted()
            ->asReviewed()
            ->build()
        ;

        // Item 2: (8 - 5) × 5.00€ = +3 × 5.00€ = +15.00€ (surplus)
        $item2 = $this->itemFactory
            ->create()
            ->withPrice(500) // 5.00€
            ->withTheoreticalStock(5.0)
            ->withRealStock(8.0)
            ->asCounted()
            ->asReviewed()
            ->build()
        ;

        $inventory->addItem($item1);
        $inventory->addItem($item2);

        $repository = $this->createMock(InventoryRepository::class);
        $stockUpdater = $this->createMock(ArticleStockUpdaterInterface::class);
        $useCase = new CompleteInventory(
            inventoryRepository: $repository,
            articleStockUpdater: $stockUpdater,
            transactionalExecutor: $this->transactionalExecutor,
        );
        $request = $this->createMock(CompleteInventoryRequest::class);

        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);
        $repository->expects(self::once())->method('getByUuid')->willReturn($inventory);
        $repository->expects(self::once())->method('save');
        $stockUpdater->expects(self::once())->method('updateStocks');

        // Act
        $response = $useCase->execute($request);

        // Assert: Total = -20.00€ + 15.00€ = -5.00€ (-500 cents, net loss)
        self::assertSame(-500, $response->discrepancyAmount->toInt());
    }

    public function testCompleteInventoryWithNoDiscrepancies(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();

        $inventory = (new InventoryFakerFactory())->createReviewed()
            ->withUuid($inventoryUuid)
            ->build()
        ;

        // Item with no discrepancy (theoretical == real)
        $item = $this->itemFactory
            ->create()
            ->withPrice(1000)
            ->withTheoreticalStock(10.0)
            ->withRealStock(10.0)
            ->asCounted()
            ->build() // No need to review if no discrepancy
        ;
        $inventory->addItem($item);

        $repository = $this->createMock(InventoryRepository::class);
        $stockUpdater = $this->createMock(ArticleStockUpdaterInterface::class);
        $useCase = new CompleteInventory(
            inventoryRepository: $repository,
            articleStockUpdater: $stockUpdater,
            transactionalExecutor: $this->transactionalExecutor,
        );
        $request = $this->createMock(CompleteInventoryRequest::class);

        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);
        $repository->expects(self::once())->method('getByUuid')->willReturn($inventory);
        $repository->expects(self::once())->method('save');
        $stockUpdater->expects(self::once())->method('updateStocks');

        // Act
        $response = $useCase->execute($request);

        // Assert
        self::assertTrue($response->inventory->status()->equals(InventoryStatus::COMPLETED));
        self::assertSame(0, $response->discrepancyAmount->toInt());
    }

    /**
     * Creates a mock that simply executes the callable directly (no real transaction).
     */
    public function testCompleteInventoryDoesNotSaveWhenStockUpdateFails(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();

        $inventory = (new InventoryFakerFactory())->createReviewed()
            ->withUuid($inventoryUuid)
            ->build()
        ;

        $reviewedItem = $this->itemFactory
            ->createWithPreciseStocks(theoreticalStock: 10.0, realStock: 8.0)
            ->asReviewed()
            ->build()
        ;
        $inventory->addItem($reviewedItem);

        $repository = $this->createMock(InventoryRepository::class);
        $stockUpdater = $this->createMock(ArticleStockUpdaterInterface::class);
        $useCase = new CompleteInventory(
            inventoryRepository: $repository,
            articleStockUpdater: $stockUpdater,
            transactionalExecutor: $this->transactionalExecutor,
        );
        $request = $this->createMock(CompleteInventoryRequest::class);

        // Assert
        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);
        $repository->expects(self::once())->method('getByUuid')->willReturn($inventory);

        $stockUpdater->expects(self::once())
            ->method('updateStocks')
            ->with(self::callback(static function (array $updates): bool {
                return \count($updates) === 1
                    && $updates[0] instanceof StockUpdateCommand
                    && $updates[0]->newQuantityMilliemes === 8000; // realStock: 8.0
            }))
            ->willThrowException(new \RuntimeException('Stock update failed'))
        ;

        $repository->expects(self::never())->method('save');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stock update failed');

        // Act
        $useCase->execute($request);
    }

    private function createPassthroughTransactionalExecutor(): TransactionalExecutorInterface
    {
        $mock = $this->createMock(TransactionalExecutorInterface::class);
        $mock->method('execute')
            ->willReturnCallback(static fn (callable $operation): mixed => $operation())
        ;

        return $mock;
    }
}
