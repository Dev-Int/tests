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

namespace Inventory\Tests\UseCases\ResumeCountingFromReview;

use Inventory\Entities\Exception\InvalidStatusTransition;
use Inventory\Entities\Exception\InventoryNotFound;
use Inventory\Entities\InventoryItem;
use Inventory\Entities\Repository\InventoryRepository;
use Inventory\Entities\VO\InventoryStatus;
use Inventory\Tests\Factory\InventoryFakerFactory;
use Inventory\Tests\Factory\InventoryItemFakerFactory;
use Inventory\UseCases\ResumeCountingFromReview\ResumeCountingFromReview;
use Inventory\UseCases\ResumeCountingFromReview\ResumeCountingFromReviewRequest;
use PHPUnit\Framework\TestCase;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\Clock\FrozenClock;
use Shared\Entities\ResourceUuid;

/**
 * @group unitTest
 *
 * @covers \Inventory\UseCases\ResumeCountingFromReview\ResumeCountingFromReview
 */
final class ResumeCountingFromReviewTest extends TestCase
{
    private InventoryItemFakerFactory $itemFactory;

    protected function setUp(): void
    {
        ClockFactory::initialize(clock: new FrozenClock(now: new \DateTimeImmutable('2025-12-01')));
        $this->itemFactory = new InventoryItemFakerFactory();
    }

    public function testResumeCountingFromReviewSuccessfully(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $zoneStorageUuid = ResourceUuid::generate();
        $inventory = (new InventoryFakerFactory())->createReviewed()
            ->withUuid($inventoryUuid)
            ->build()
        ;

        $repository = $this->createMock(InventoryRepository::class);
        $useCase = new ResumeCountingFromReview(inventoryRepository: $repository);
        $request = $this->createMock(ResumeCountingFromReviewRequest::class);

        // Assert setup
        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);
        $request->expects(self::once())->method('zoneStorageUuid')->willReturn($zoneStorageUuid);

        $repository->expects(self::once())->method('getByUuid')->with($inventoryUuid)->willReturn($inventory);
        $repository->expects(self::once())->method('save')->with($inventory);

        // Act
        $response = $useCase->execute($request);

        // Assert
        self::assertTrue($response->inventory->status()->equals(InventoryStatus::IN_PROGRESS));
    }

    public function testResumeCountingThrowsExceptionWhenInventoryNotFound(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();

        $repository = $this->createMock(InventoryRepository::class);
        $useCase = new ResumeCountingFromReview(inventoryRepository: $repository);
        $request = $this->createMock(ResumeCountingFromReviewRequest::class);

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

    public function testResumeCountingThrowsExceptionWhenNotInReview(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $zoneStorageUuid = ResourceUuid::generate();
        $inventory = (new InventoryFakerFactory())->createInProgress()
            ->withUuid($inventoryUuid)
            ->build()
        ;

        $repository = $this->createMock(InventoryRepository::class);
        $useCase = new ResumeCountingFromReview(inventoryRepository: $repository);
        $request = $this->createMock(ResumeCountingFromReviewRequest::class);

        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);
        $request->expects(self::once())->method('zoneStorageUuid')->willReturn($zoneStorageUuid);

        $repository->expects(self::once())->method('getByUuid')->with($inventoryUuid)->willReturn($inventory);
        $repository->expects(self::never())->method('save');

        $this->expectException(InvalidStatusTransition::class);

        // Act
        $useCase->execute($request);
    }

    public function testResumeCountingThrowsExceptionWhenAlreadyCompleted(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $zoneStorageUuid = ResourceUuid::generate();
        $inventory = (new InventoryFakerFactory())->createCompleted()
            ->withUuid($inventoryUuid)
            ->build()
        ;

        $repository = $this->createMock(InventoryRepository::class);
        $useCase = new ResumeCountingFromReview(inventoryRepository: $repository);
        $request = $this->createMock(ResumeCountingFromReviewRequest::class);

        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);
        $request->expects(self::once())->method('zoneStorageUuid')->willReturn($zoneStorageUuid);

        $repository->expects(self::once())->method('getByUuid')->with($inventoryUuid)->willReturn($inventory);
        $repository->expects(self::never())->method('save');

        $this->expectException(InvalidStatusTransition::class);

        // Act
        $useCase->execute($request);
    }

    public function testResumeCountingThrowsExceptionWhenAlreadyCancelled(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $zoneStorageUuid = ResourceUuid::generate();
        $inventory = (new InventoryFakerFactory())->createCancelled()
            ->withUuid($inventoryUuid)
            ->build()
        ;

        $repository = $this->createMock(InventoryRepository::class);
        $useCase = new ResumeCountingFromReview(inventoryRepository: $repository);
        $request = $this->createMock(ResumeCountingFromReviewRequest::class);

        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);
        $request->expects(self::once())->method('zoneStorageUuid')->willReturn($zoneStorageUuid);

        $repository->expects(self::once())->method('getByUuid')->with($inventoryUuid)->willReturn($inventory);
        $repository->expects(self::never())->method('save');

        $this->expectException(InvalidStatusTransition::class);

        // Act
        $useCase->execute($request);
    }

    public function testResumeCountingPreservesItemsWithRealStock(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $zoneStorageUuid = ResourceUuid::generate();
        $inventory = (new InventoryFakerFactory())->createReviewed()
            ->withUuid($inventoryUuid)
            ->build()
        ;

        // Add items with stocks
        $item = $this->itemFactory
            ->createWithPreciseStocks(theoreticalStock: 10.0, realStock: 8.0, zoneStorage: $zoneStorageUuid)
            ->build()
        ;
        $inventory->addItem($item);

        $repository = $this->createMock(InventoryRepository::class);
        $useCase = new ResumeCountingFromReview(inventoryRepository: $repository);
        $request = $this->createMock(ResumeCountingFromReviewRequest::class);

        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);
        $request->expects(self::once())->method('zoneStorageUuid')->willReturn($zoneStorageUuid);

        $repository->expects(self::once())->method('getByUuid')->with($inventoryUuid)->willReturn($inventory);
        $repository->expects(self::once())->method('save')->with($inventory);

        // Act
        $response = $useCase->execute($request);

        // Assert - items should remain with their realStock values
        self::assertCount(1, $response->inventory->items());

        /** @var InventoryItem $savedItem */
        $savedItem = iterator_to_array($response->inventory->items())[0];
        self::assertEquals(8.0, $savedItem->realStock()->toUnit());
    }

    public function testResumeCountingResetsReviewedFlagsForZone(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $zoneStorageUuid = ResourceUuid::generate();
        $otherZoneUuid = ResourceUuid::generate();
        $inventory = (new InventoryFakerFactory())->createReviewed()
            ->withUuid($inventoryUuid)
            ->build()
        ;

        $reviewedItem = $this->itemFactory
            ->createWithPreciseStocks(theoreticalStock: 10.0, realStock: 8.0, zoneStorage: $zoneStorageUuid)
            ->build()
            ->withReviewed(true)
        ;
        $inventory->addItem($reviewedItem);

        $otherZoneItem = $this->itemFactory
            ->createWithPreciseStocks(theoreticalStock: 5.0, realStock: 3.0, zoneStorage: $otherZoneUuid)
            ->build()
            ->withReviewed(true)
        ;
        $inventory->addItem($otherZoneItem);

        $repository = $this->createMock(InventoryRepository::class);
        $useCase = new ResumeCountingFromReview(inventoryRepository: $repository);
        $request = $this->createMock(ResumeCountingFromReviewRequest::class);

        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);
        $request->expects(self::once())->method('zoneStorageUuid')->willReturn($zoneStorageUuid);

        $repository->expects(self::once())->method('getByUuid')->with($inventoryUuid)->willReturn($inventory);
        $repository->expects(self::once())->method('save')->with($inventory);

        // Act
        $response = $useCase->execute($request);

        // Assert
        $items = iterator_to_array($response->inventory->items());

        $targetZoneItem = array_filter($items, static fn (InventoryItem $i) => $i->isForZone($zoneStorageUuid));
        self::assertFalse(array_values($targetZoneItem)[0]->isReviewed());

        $otherItem = array_filter($items, static fn (InventoryItem $i) => $i->isForZone($otherZoneUuid));
        self::assertTrue(array_values($otherItem)[0]->isReviewed());
    }
}
