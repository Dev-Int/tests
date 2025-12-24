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

use Inventory\Entities\Exception\EqualOrFutureDateExpected;
use Inventory\Entities\Exception\InventoryAlreadyActiveForZone;
use Inventory\Entities\Inventory;
use Inventory\Entities\Repository\InventoryRepository;
use Inventory\Entities\VO\InventoryDate;
use Inventory\Entities\VO\InventoryStatus;
use Inventory\Entities\VO\ZoneStorage;
use Inventory\UseCases\CreateInventory\CreateInventory;
use Inventory\UseCases\CreateInventory\CreateInventoryRequest;
use Inventory\UseCases\Gateway\ZoneStorageGatewayInterface;
use PHPUnit\Framework\TestCase;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\Clock\FrozenClock;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\NameField;

use function PHPUnit\Framework\assertCount;

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
        ClockFactory::initialize(new FrozenClock(new \DateTimeImmutable('2025-12-01')));
        $now = ClockFactory::clock()->now();

        yield 'date in the past' => [
            'invalidDate' => $now->modify('-1 year'),
            'expectedException' => EqualOrFutureDateExpected::class,
            'expectedMessage' => 'Inventory date must be today or in the future.',
        ];

        yield 'date yesterday' => [
            'invalidDate' => $now->modify('yesterday'),
            'expectedException' => EqualOrFutureDateExpected::class,
            'expectedMessage' => 'Inventory date must be today or in the future.',
        ];
    }

    public function testCreateInventoryWithSuccess(): void
    {
        // Arrange
        ClockFactory::initialize(new FrozenClock(new \DateTimeImmutable('2025-12-01')));
        $inventoryRepository = $this->createMock(InventoryRepository::class);
        $zoneStorageGateway = $this->createMock(ZoneStorageGatewayInterface::class);
        $useCase = new CreateInventory($inventoryRepository, $zoneStorageGateway);
        $request = $this->createMock(CreateInventoryRequest::class);

        $date = new \DateTimeImmutable('2025-12-20');
        $zoneStorage = new ZoneStorage(ResourceUuid::generate(), NameField::fromString('Zone de stockage'));
        $zoneStorageId = $zoneStorage->uuid;
        $inventoryId = ResourceUuid::generate();

        // Assert
        $request->expects(self::once())->method('uuid')->willReturn($inventoryId);
        $request->expects(self::once())->method('date')->willReturn($date);
        $request->expects(self::once())->method('zoneStorages')->willReturn([$zoneStorage]);

        $inventoryRepository->expects(self::once())
            ->method('hasActiveForZone')
            ->with([$zoneStorageId])
            ->willReturn(false)
        ;
        $zoneStorageGateway->expects(self::never())->method('provideAll');
        $inventoryRepository->expects(self::once())
            ->method('create')
            ->with(Inventory::create($inventoryId, [$zoneStorage], InventoryDate::fromDateTimeImmutable($date)))
        ;

        // Act
        $response = $useCase->execute($request);
        $inventory = $response->inventory;

        // Assert
        self::assertSame($date, $inventory->date()->toDateTimeImmutable());
        $zoneStorages = $inventory->zoneStorages();
        self::assertCount(1, $zoneStorages);
        self::assertSame($zoneStorage->uuid->toString(), $zoneStorages[0]->uuid->toString());
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
        $zoneStorageGateway = $this->createMock(ZoneStorageGatewayInterface::class);
        $useCase = new CreateInventory($inventoryRepository, $zoneStorageGateway);
        $request = $this->createMock(CreateInventoryRequest::class);

        $zoneStorage = new ZoneStorage(ResourceUuid::generate(), NameField::fromString('Zone de stockage'));
        $inventoryId = ResourceUuid::generate();

        // Assert
        $request->expects(self::never())->method('uuid')->willReturn($inventoryId);
        $request->expects(self::once())->method('date')->willReturn($invalidDate);
        $request->expects(self::never())->method('zoneStorages')->willReturn([$zoneStorage]);

        $inventoryRepository->expects(self::never())->method('hasActiveForZone');
        $zoneStorageGateway->expects(self::never())->method('provideAll');
        $inventoryRepository->expects(self::never())->method('create');

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
        $zoneStorageGateway = $this->createMock(ZoneStorageGatewayInterface::class);
        $useCase = new CreateInventory($inventoryRepository, $zoneStorageGateway);
        $request = $this->createMock(CreateInventoryRequest::class);

        $date = new \DateTimeImmutable('2025-12-20');
        $zoneStorage = new ZoneStorage(ResourceUuid::generate(), NameField::fromString('Zone de stockage'));
        $zoneStorageId = $zoneStorage->uuid;
        $inventoryId = ResourceUuid::generate();

        $request->expects(self::never())->method('uuid')->willReturn($inventoryId);
        $request->expects(self::once())->method('date')->willReturn($date);
        $request->expects(self::once())->method('zoneStorages')->willReturn([$zoneStorage]);

        $inventoryRepository->expects(self::once())
            ->method('hasActiveForZone')
            ->with([$zoneStorageId])
            ->willReturn(true)
        ;
        $zoneStorageGateway->expects(self::never())->method('provideAll');

        $inventoryRepository->expects(self::never())->method('create');

        // Assert
        $this->expectException(InventoryAlreadyActiveForZone::class);
        $this->expectExceptionMessage(InventoryAlreadyActiveForZone::MESSAGE);

        // Act
        $useCase->execute($request);
    }

    public function testMultipleInventoriesCanBeCreatedForDifferentZones(): void
    {
        // Arrange
        ClockFactory::initialize(new FrozenClock(new \DateTimeImmutable('2025-12-01')));

        $inventoryRepository = $this->createMock(InventoryRepository::class);
        $zoneStorageGateway = $this->createMock(ZoneStorageGatewayInterface::class);
        $useCase = new CreateInventory($inventoryRepository, $zoneStorageGateway);

        $date = new \DateTimeImmutable('2025-12-20');
        $zoneStorage1 = new ZoneStorage(ResourceUuid::generate(), NameField::fromString('zone 1'));
        $zoneStorage2 = new ZoneStorage(ResourceUuid::generate(), NameField::fromString('zone 2'));
        $zoneStorage1Ids = [$zoneStorage1->uuid];
        $zoneStorage2Ids = [$zoneStorage2->uuid];

        $inventory1Id = ResourceUuid::generate();
        $inventory2Id = ResourceUuid::generate();

        // Premier inventaire pour zone 1
        $request1 = $this->createMock(CreateInventoryRequest::class);
        $request1->expects(self::once())->method('uuid')->willReturn($inventory1Id);
        $request1->expects(self::once())->method('date')->willReturn($date);
        $request1->expects(self::once())->method('zoneStorages')->willReturn([$zoneStorage1]);

        $hasActiveInvocations = 0;
        $inventoryRepository->expects(self::exactly(2))
            ->method('hasActiveForZone')
            ->willReturnCallback(static function ($ids) use (&$hasActiveInvocations, $zoneStorage1Ids, $zoneStorage2Ids) {
                ++$hasActiveInvocations;
                if (1 === $hasActiveInvocations) {
                    self::assertEquals($zoneStorage1Ids, $ids);
                } elseif (2 === $hasActiveInvocations) {
                    self::assertEquals($zoneStorage2Ids, $ids);
                }

                return false;
            })
        ;
        $zoneStorageGateway->expects(self::never())->method('provideAll');
        $saveInvocations = 0;
        $inventoryRepository->expects(self::exactly(2))
            ->method('create')
            ->willReturnCallback(
                static function ($inventory) use (&$saveInvocations, $inventory1Id, $inventory2Id, $zoneStorage1, $zoneStorage2, $date): void {
                    ++$saveInvocations;
                    if (1 === $saveInvocations) {
                        $expected = Inventory::create(
                            $inventory1Id,
                            [$zoneStorage1],
                            InventoryDate::fromDateTimeImmutable($date)
                        );
                        self::assertEquals($expected, $inventory);
                    } elseif (2 === $saveInvocations) {
                        $expected = Inventory::create(
                            $inventory2Id,
                            [$zoneStorage2],
                            InventoryDate::fromDateTimeImmutable($date)
                        );
                        self::assertEquals($expected, $inventory);
                    }
                }
            )
        ;

        // Act
        $response1 = $useCase->execute($request1);

        // Deuxième inventaire pour zone 2
        $request2 = $this->createMock(CreateInventoryRequest::class);
        $request2->expects(self::once())->method('uuid')->willReturn($inventory2Id);
        $request2->expects(self::once())->method('date')->willReturn($date);
        $request2->expects(self::once())->method('zoneStorages')->willReturn([$zoneStorage2]);

        $response2 = $useCase->execute($request2);

        // Assert
        $zoneStorages1 = $response1->inventory->zoneStorages();
        assertCount(1, $zoneStorages1);
        self::assertSame($zoneStorage1->uuid->toString(), $zoneStorages1[0]->uuid->toString());
        $zoneStorages2 = $response2->inventory->zoneStorages();
        assertCount(1, $zoneStorages2);
        self::assertSame($zoneStorage2->uuid->toString(), $zoneStorages2[0]->uuid->toString());
        self::assertNotSame(
            $response1->inventory->uuid()->toString(),
            $response2->inventory->uuid()->toString()
        );
    }

    public function testInventoryIsCreatedWithEmptyZoneStorageIds(): void
    {
        // Arrange
        ClockFactory::initialize(new FrozenClock(new \DateTimeImmutable('2025-12-01')));
        $inventoryRepository = $this->createMock(InventoryRepository::class);
        $zoneStorageGateway = $this->createMock(ZoneStorageGatewayInterface::class);
        $useCase = new CreateInventory($inventoryRepository, $zoneStorageGateway);
        $request = $this->createMock(CreateInventoryRequest::class);

        $date = new \DateTimeImmutable('2025-12-20');
        $zoneStorage1 = new ZoneStorage(ResourceUuid::generate(), NameField::fromString('zone 1'));
        $zoneStorage2 = new ZoneStorage(ResourceUuid::generate(), NameField::fromString('zone 2'));
        $inventoryId = ResourceUuid::generate();

        $request->expects(self::once())->method('uuid')->willReturn($inventoryId);
        $request->expects(self::once())->method('date')->willReturn($date);
        $request->expects(self::once())->method('zoneStorages')->willReturn([]);

        $inventoryRepository->expects(self::once())
            ->method('hasActiveForZone')
            ->willReturn(false)
        ;
        $zoneStorageGateway->expects(self::once())
            ->method('provideAll')
            ->willReturn([$zoneStorage1, $zoneStorage2])
        ;
        $inventoryRepository->expects(self::once())->method('create');

        // Act
        $response = $useCase->execute($request);
        $inventory = $response->inventory;

        // Assert
        self::assertCount(2, $inventory->zoneStorages());
    }
}
