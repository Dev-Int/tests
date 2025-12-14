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

namespace Inventory\Tests\UseCases\CreateInventory;

use Inventory\Entities\Exception\InventoryAlreadyActiveForZone;
use Inventory\Entities\Exception\PastDateExpected;
use Inventory\Entities\Inventory;
use Inventory\Entities\Repository\InventoryRepository;
use Inventory\Entities\VO\InventoryStatus;
use Inventory\UseCases\CreateInventory\CreateInventory;
use Inventory\UseCases\CreateInventory\CreateInventoryRequest;
use PHPUnit\Framework\TestCase;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\Clock\FrozenClock;
use Shared\Entities\ResourceUuid;

/**
 * @group unitTest
 *
 * @covers \Inventory\UseCases\CreateInventory\CreateInventory
 */
final class CreateInventoryTest extends TestCase
{
    /**
     * @return iterable<string, array{
     *     invalidDate: \DateTimeImmutable,
     *     expectedException: class-string<\Throwable>,
     *     expectedMessage: string
     * }>
     */
    public static function provideCreateInventoryFailsWithInvalidDateCases(): iterable
    {
        yield 'date in the past' => [
            'invalidDate' => new \DateTimeImmutable('2020-01-01'),
            'expectedException' => PastDateExpected::class,
            'expectedMessage' => 'Inventory date must be today or in the future.',
        ];

        yield 'date yesterday' => [
            'invalidDate' => new \DateTimeImmutable('yesterday'),
            'expectedException' => PastDateExpected::class,
            'expectedMessage' => 'Inventory date must be today or in the future.',
        ];
    }

    public function testCreateInventoryWithSuccess(): void
    {
        // Arrange
        $inventoryRepository = $this->createMock(InventoryRepository::class);
        $useCase = new CreateInventory($inventoryRepository);
        $request = $this->createMock(CreateInventoryRequest::class);

        $date = new \DateTimeImmutable('2025-12-20');
        $zoneStorageUuid = ResourceUuid::generate();
        $inventoryId = ResourceUuid::generate();

        $request->expects(self::once())->method('uuid')->willReturn($inventoryId);
        $request->expects(self::once())->method('date')->willReturn($date);
        $request->expects(self::exactly(2))->method('zoneStorages')->willReturn([$zoneStorageUuid]);

        $inventoryRepository->expects(self::once())
            ->method('hasActiveForZone')
            ->with([$zoneStorageUuid])
            ->willReturn(false)
        ;

        $inventoryRepository->expects(self::once())
            ->method('save')
            ->with(Inventory::create($inventoryId, [$zoneStorageUuid], $date))
        ;

        // Act
        $response = $useCase->execute($request);
        $inventory = $response->inventory;

        // Assert
        self::assertSame($date, $inventory->date());
        self::assertSame($zoneStorageUuid->toString(), $inventory->zoneStorageIds()[0]->toString());
        self::assertTrue($inventory->status()->equals(InventoryStatus::DRAFT));
        self::assertSame(0, $inventory->amount()->toInt());
    }

    /**
     * @dataProvider provideCreateInventoryFailsWithInvalidDateCases
     *
     * @param class-string<\Throwable> $expectedException
     */
    public function testCreateInventoryFailsWithInvalidDate(
        \DateTimeImmutable $invalidDate,
        string $expectedException,
        string $expectedMessage
    ): void {
        // Arrange
        $inventoryRepository = $this->createMock(InventoryRepository::class);
        $useCase = new CreateInventory($inventoryRepository);
        $request = $this->createMock(CreateInventoryRequest::class);

        $zoneStorageUuid = ResourceUuid::generate();
        $inventoryId = ResourceUuid::generate();

        $request->expects(self::never())->method('uuid')->willReturn($inventoryId);
        $request->expects(self::once())->method('date')->willReturn($invalidDate);
        $request->expects(self::never())->method('zoneStorages')->willReturn([$zoneStorageUuid]);

        $inventoryRepository->expects(self::never())->method('hasActiveForZone');
        $inventoryRepository->expects(self::never())->method('save');

        // Assert
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedMessage);

        // Act
        $useCase->execute($request);
    }

    public function testCreateInventoryFailsWhenActiveInventoryAlreadyExists(): void
    {
        // Arrange
        ClockFactory::initialize(new FrozenClock(new \DateTimeImmutable('2025-12-01')));
        $inventoryRepository = $this->createMock(InventoryRepository::class);
        $useCase = new CreateInventory($inventoryRepository);
        $request = $this->createMock(CreateInventoryRequest::class);

        $date = new \DateTimeImmutable('2025-12-20');
        $zoneStorageUuid = ResourceUuid::generate();
        $inventoryId = ResourceUuid::generate();

        $request->expects(self::never())->method('uuid')->willReturn($inventoryId);
        $request->expects(self::once())->method('date')->willReturn($date);
        $request->expects(self::exactly(2))->method('zoneStorages')->willReturn([$zoneStorageUuid]);

        $inventoryRepository->expects(self::once())
            ->method('hasActiveForZone')
            ->with([$zoneStorageUuid])
            ->willReturn(true)
        ;

        $inventoryRepository->expects(self::never())->method('save');

        // Assert
        $this->expectException(InventoryAlreadyActiveForZone::class);
        $this->expectExceptionMessage(InventoryAlreadyActiveForZone::MESSAGE);

        // Act
        $useCase->execute($request);
    }

    public function testMultipleInventoriesCanBeCreatedForDifferentZones(): void
    {
        // Arrange
        $inventoryRepository = $this->createMock(InventoryRepository::class);
        $useCase = new CreateInventory($inventoryRepository);

        $date = new \DateTimeImmutable('2025-12-20');
        ClockFactory::initialize(new FrozenClock($date));
        $zone1Uuid = ResourceUuid::generate();
        $zone2Uuid = ResourceUuid::generate();
        $inventory1Id = ResourceUuid::generate();
        $inventory2Id = ResourceUuid::generate();

        // Premier inventaire pour zone 1
        $request1 = $this->createMock(CreateInventoryRequest::class);
        $request1->expects(self::once())->method('uuid')->willReturn($inventory1Id);
        $request1->expects(self::once())->method('date')->willReturn($date);
        $request1->expects(self::exactly(2))->method('zoneStorages')->willReturn([$zone1Uuid]);

        $inventoryRepository->expects(self::exactly(2))
            ->method('hasActiveForZone')
            ->withConsecutive([[$zone1Uuid]], [[$zone2Uuid]])
            ->willReturnOnConsecutiveCalls(false, false)
        ;

        $inventoryRepository->expects(self::exactly(2))
            ->method('save')
            ->withConsecutive(
                [Inventory::create($inventory1Id, [$zone1Uuid], $date)],
                [Inventory::create($inventory2Id, [$zone2Uuid], $date)]
            )
        ;

        // Act
        $response1 = $useCase->execute($request1);

        // Deuxième inventaire pour zone 2
        $request2 = $this->createMock(CreateInventoryRequest::class);
        $request2->expects(self::once())->method('uuid')->willReturn($inventory2Id);
        $request2->expects(self::once())->method('date')->willReturn($date);
        $request2->expects(self::exactly(2))->method('zoneStorages')->willReturn([$zone2Uuid]);

        $response2 = $useCase->execute($request2);

        // Assert
        self::assertSame($zone1Uuid->toString(), $response1->inventory->zoneStorageIds()[0]->toString());
        self::assertSame($zone2Uuid->toString(), $response2->inventory->zoneStorageIds()[0]->toString());
        self::assertNotSame(
            $response1->inventory->uuid()->toString(),
            $response2->inventory->uuid()->toString()
        );
    }

    public function testInventoryIsCreatedWithEmptyItemsCollection(): void
    {
        // Arrange
        $inventoryRepository = $this->createMock(InventoryRepository::class);
        $useCase = new CreateInventory($inventoryRepository);
        $request = $this->createMock(CreateInventoryRequest::class);

        $date = new \DateTimeImmutable('2025-12-20');
        $zoneStorageUuid = ResourceUuid::generate();
        $inventoryId = ResourceUuid::generate();

        $request->expects(self::once())->method('uuid')->willReturn($inventoryId);
        $request->expects(self::once())->method('date')->willReturn($date);
        $request->expects(self::exactly(2))->method('zoneStorages')->willReturn([$zoneStorageUuid]);

        $inventoryRepository->expects(self::once())
            ->method('hasActiveForZone')
            ->willReturn(false)
        ;

        $inventoryRepository->expects(self::once())->method('save');

        // Act
        $response = $useCase->execute($request);
        $inventory = $response->inventory;

        // Assert
        self::assertCount(0, $inventory->items());
    }
}
