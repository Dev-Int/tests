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

namespace Inventory\Tests\UseCases\FinishCounting;

use Inventory\Entities\Exception\IncompleteInventoryCounting;
use Inventory\Entities\Exception\InvalidStatusTransition;
use Inventory\Entities\Exception\InventoryNotFound;
use Inventory\Entities\Repository\InventoryRepository;
use Inventory\Entities\VO\InventoryStatus;
use Inventory\Tests\Factory\InventoryFakerFactory;
use Inventory\Tests\Factory\InventoryItemFakerFactory;
use Inventory\UseCases\FinishCounting\FinishCounting;
use Inventory\UseCases\FinishCounting\FinishCountingRequest;
use PHPUnit\Framework\TestCase;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\Clock\FrozenClock;
use Shared\Entities\ResourceUuid;

/**
 * @group unitTest
 *
 * @covers \Inventory\UseCases\FinishCounting\FinishCounting
 */
final class FinishCountingTest extends TestCase
{
    private InventoryItemFakerFactory $itemFactory;

    protected function setUp(): void
    {
        ClockFactory::initialize(clock: new FrozenClock(now: new \DateTimeImmutable('2025-12-01')));
        $this->itemFactory = new InventoryItemFakerFactory();
    }

    public function testFinishCountingSuccessfullyTransitionsToReview(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $inventory = (new InventoryFakerFactory())->createInProgress()
            ->withUuid($inventoryUuid)
            ->build()
        ;

        // Add counted item with discrepancy
        $countedItem = $this->itemFactory->createWithPreciseStocks(theoreticalStock: 10.0, realStock: 8.0)->build();
        $inventory->addItem($countedItem);
        // Add counted item without discrepancy
        $noDiscrepancy = $this->itemFactory->createWithPreciseStocks(theoreticalStock: 5.0, realStock: 5.0)->build();
        $inventory->addItem($noDiscrepancy);

        $repository = $this->createMock(InventoryRepository::class);
        $useCase = new FinishCounting(inventoryRepository: $repository);
        $request = $this->createMock(FinishCountingRequest::class);

        // Assert
        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);

        $repository->expects(self::once())->method('getByUuid')->willReturn($inventory);
        $repository->expects(self::once())->method('save');

        // Act
        $response = $useCase->execute($request);

        // Assert
        self::assertTrue($response->inventory->status()->equals(InventoryStatus::REVIEW));
        self::assertSame(1, $response->discrepancyCount);
        self::assertCount(1, $response->itemsWithDiscrepancies);
    }

    public function testFinishCountingThrowsExceptionWhenInventoryNotFound(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();

        $repository = $this->createMock(InventoryRepository::class);
        $useCase = new FinishCounting(inventoryRepository: $repository);
        $request = $this->createMock(FinishCountingRequest::class);

        // Assert
        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);

        $repository->expects(self::once())
            ->method('getByUuid')
            ->willThrowException(new InventoryNotFound(inventoryUuid: $inventoryUuid))
        ;
        $repository->expects(self::never())->method('save');

        $this->expectException(InventoryNotFound::class);

        // Act
        $useCase->execute($request);
    }

    public function testFinishCountingThrowsExceptionWhenNotInProgress(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $inventory = (new InventoryFakerFactory())->createDraft()
            ->withUuid($inventoryUuid)
            ->build()
        ;

        $repository = $this->createMock(InventoryRepository::class);
        $useCase = new FinishCounting(inventoryRepository: $repository);
        $request = $this->createMock(FinishCountingRequest::class);

        // Assert
        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);

        $repository->expects(self::once())->method('getByUuid')->willReturn($inventory);
        $repository->expects(self::never())->method('save');

        $this->expectException(InvalidStatusTransition::class);

        // Act
        $useCase->execute($request);
    }

    public function testFinishCountingThrowsExceptionWhenItemsNotCounted(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $inventory = (new InventoryFakerFactory())->createInProgress()
            ->withUuid($inventoryUuid)
            ->build()
        ;
        // Add uncounted item (no withRealStock called after build)
        $inventory->addItem($this->itemFactory->create()->build());

        $repository = $this->createMock(InventoryRepository::class);
        $useCase = new FinishCounting(inventoryRepository: $repository);
        $request = $this->createMock(FinishCountingRequest::class);

        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);

        $repository->expects(self::once())->method('getByUuid')->willReturn($inventory);
        $repository->expects(self::never())->method('save');

        $this->expectException(IncompleteInventoryCounting::class);

        // Act
        $useCase->execute($request);
    }

    public function testFinishCountingReturnsCorrectDiscrepancyData(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $inventory = (new InventoryFakerFactory())->createInProgress()
            ->withUuid($inventoryUuid)
            ->build()
        ;

        // Add 3 items: 2 with discrepancies, 1 without
        $shortage = $this->itemFactory->createWithPreciseStocks(theoreticalStock: 10.0, realStock: 7.0)->build();
        $surplus = $this->itemFactory->createWithPreciseStocks(theoreticalStock: 5.0, realStock: 8.0)->build();
        $equal = $this->itemFactory->createWithPreciseStocks(theoreticalStock: 15.0, realStock: 15.0)->build();

        $inventory->addItem($shortage);
        $inventory->addItem($surplus);
        $inventory->addItem($equal);

        $repository = $this->createMock(InventoryRepository::class);
        $useCase = new FinishCounting(inventoryRepository: $repository);
        $request = $this->createMock(FinishCountingRequest::class);

        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);

        $repository->expects(self::once())->method('getByUuid')->willReturn($inventory);
        $repository->expects(self::once())->method('save');

        // Act
        $response = $useCase->execute($request);

        // Assert
        self::assertSame(2, $response->discrepancyCount);
        self::assertCount(2, $response->itemsWithDiscrepancies);
    }
}
