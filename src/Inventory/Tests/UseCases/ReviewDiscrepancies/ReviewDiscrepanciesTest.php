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

namespace Inventory\Tests\UseCases\ReviewDiscrepancies;

use Inventory\Entities\Exception\CannotReviewItemOnNonReviewInventory;
use Inventory\Entities\Exception\InventoryNotFound;
use Inventory\Entities\Repository\InventoryRepository;
use Inventory\Tests\Factory\InventoryFakerFactory;
use Inventory\Tests\Factory\InventoryItemFakerFactory;
use Inventory\UseCases\ReviewDiscrepancies\ReviewDiscrepancies;
use Inventory\UseCases\ReviewDiscrepancies\ReviewDiscrepanciesRequest;
use PHPUnit\Framework\TestCase;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\Clock\FrozenClock;
use Shared\Entities\ResourceUuid;

/**
 * @group unitTest
 *
 * @covers \Inventory\UseCases\ReviewDiscrepancies\ReviewDiscrepancies
 */
final class ReviewDiscrepanciesTest extends TestCase
{
    private InventoryItemFakerFactory $itemFactory;

    protected function setUp(): void
    {
        ClockFactory::initialize(clock: new FrozenClock(now: new \DateTimeImmutable('2025-12-01')));
        $this->itemFactory = new InventoryItemFakerFactory();
    }

    public function testReviewDiscrepanciesSuccessfullyMarksItems(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $articleUuid = ResourceUuid::generate();
        $zoneStorageUuid = ResourceUuid::generate();

        $inventory = (new InventoryFakerFactory())->createReviewed()
            ->withUuid($inventoryUuid)
            ->build()
        ;

        // Add item with discrepancy
        $inventory->addItem(
            $this->itemFactory->create($articleUuid, $zoneStorageUuid)
                ->withTheoreticalStock(10.0)
                ->withRealStock(8.0)
                ->asCounted()
                ->build()
        );

        $repository = $this->createMock(InventoryRepository::class);
        $useCase = new ReviewDiscrepancies(inventoryRepository: $repository);
        $request = $this->createMock(ReviewDiscrepanciesRequest::class);

        // Assert
        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);
        $request->expects(self::once())->method('itemIdentifiers')->willReturn([
            ['articleUuid' => $articleUuid, 'zoneStorageUuid' => $zoneStorageUuid],
        ]);

        $repository->expects(self::once())->method('getByUuid')->willReturn($inventory);
        $repository->expects(self::once())->method('save');

        // Act
        $response = $useCase->execute($request);

        // Assert
        self::assertSame(1, $response->reviewedCount);
        self::assertCount(1, $response->reviewedItems);
        self::assertTrue($response->reviewedItems[0]->isReviewed());
    }

    public function testReviewDiscrepanciesThrowsExceptionWhenInventoryNotFound(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();

        $repository = $this->createMock(InventoryRepository::class);
        $useCase = new ReviewDiscrepancies(inventoryRepository: $repository);
        $request = $this->createMock(ReviewDiscrepanciesRequest::class);

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

    public function testReviewDiscrepanciesThrowsExceptionWhenNotInReviewStatus(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $articleUuid = ResourceUuid::generate();
        $zoneStorageUuid = ResourceUuid::generate();

        $inventory = (new InventoryFakerFactory())->createInProgress()
            ->withUuid($inventoryUuid)
            ->build()
        ;

        $inventory->addItem(
            $this->itemFactory->create($articleUuid, $zoneStorageUuid)
                ->withTheoreticalStock(10.0)
                ->withRealStock(8.0)
                ->asCounted()
                ->build()
        );

        $repository = $this->createMock(InventoryRepository::class);
        $useCase = new ReviewDiscrepancies(inventoryRepository: $repository);
        $request = $this->createMock(ReviewDiscrepanciesRequest::class);

        // Assert
        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);
        $request->expects(self::once())->method('itemIdentifiers')->willReturn([
            ['articleUuid' => $articleUuid, 'zoneStorageUuid' => $zoneStorageUuid],
        ]);

        $repository->expects(self::once())->method('getByUuid')->willReturn($inventory);
        $repository->expects(self::never())->method('save');

        $this->expectException(CannotReviewItemOnNonReviewInventory::class);

        // Act
        $useCase->execute($request);
    }

    public function testRepositorySaveIsCalledOnSuccess(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $articleUuid = ResourceUuid::generate();
        $zoneStorageUuid = ResourceUuid::generate();

        $inventory = (new InventoryFakerFactory())->createReviewed()
            ->withUuid($inventoryUuid)
            ->build()
        ;

        $inventory->addItem(
            $this->itemFactory->create($articleUuid, $zoneStorageUuid)
                ->withTheoreticalStock(10.0)
                ->withRealStock(8.0)
                ->asCounted()
                ->build()
        );

        $repository = $this->createMock(InventoryRepository::class);
        $useCase = new ReviewDiscrepancies(inventoryRepository: $repository);
        $request = $this->createMock(ReviewDiscrepanciesRequest::class);

        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);
        $request->expects(self::once())->method('itemIdentifiers')->willReturn([
            ['articleUuid' => $articleUuid, 'zoneStorageUuid' => $zoneStorageUuid],
        ]);

        $repository->expects(self::once())->method('getByUuid')->willReturn($inventory);

        // Critical assertion - save must be called
        $repository->expects(self::once())->method('save')->with($inventory);

        // Act
        $useCase->execute($request);
    }
}
