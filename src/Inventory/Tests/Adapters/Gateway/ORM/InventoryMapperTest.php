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

namespace Inventory\Tests\Adapters\Gateway\ORM;

use Inventory\Adapters\Gateway\ORM\Entity\Inventory as InventoryOrm;
use Inventory\Adapters\Gateway\ORM\Entity\InventoryItem;
use Inventory\Adapters\Gateway\ORM\Entity\InventoryStatus as InventoryStatusOrm;
use Inventory\Adapters\Gateway\ORM\InventoryMapper;
use Inventory\Entities\Inventory;
use Inventory\Entities\VO\InventoryDate;
use Inventory\Entities\VO\ZoneStorage;
use Inventory\Tests\Factory\InventoryFakerFactory;
use Inventory\Tests\Factory\InventoryItemFakerFactory;
use Inventory\UseCases\Gateway\ZoneStorageGatewayInterface;
use PHPUnit\Framework\TestCase;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\Clock\FrozenClock;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\NameField;

/**
 * @group unitTest
 *
 * @covers \Inventory\Adapters\Gateway\ORM\InventoryMapper
 */
final class InventoryMapperTest extends TestCase
{
    private InventoryItemFakerFactory $itemFactory;

    protected function setUp(): void
    {
        ClockFactory::initialize(new FrozenClock(new \DateTimeImmutable('2025-12-21 14:00:00')));
        $this->itemFactory = new InventoryItemFakerFactory();
    }

    public function testMapsFromDomainToOrmWithNullableSettledAt(): void
    {
        // Arrange
        $zoneStorageGateway = $this->createMock(ZoneStorageGatewayInterface::class);
        $mapper = new InventoryMapper($zoneStorageGateway);
        $inventory = (new InventoryFakerFactory())->createDraft()->build();

        // Act
        $inventoryOrm = $mapper->fromDomain($inventory);

        // Assert
        self::assertNull($inventoryOrm->statusUpdatedAt(), 'settled_at should be null for DRAFT status');
        self::assertSame($inventory->uuid()->toString(), $inventoryOrm->uuid());
        self::assertEquals($inventory->date()->toDateTimeImmutable(), $inventoryOrm->date());
    }

    public function testMapsFromDomainToOrmWithSettledAt(): void
    {
        // Arrange
        $zoneStorageGateway = $this->createMock(ZoneStorageGatewayInterface::class);
        $mapper = new InventoryMapper($zoneStorageGateway);
        $inventory = (new InventoryFakerFactory())->createInProgress()->build();

        // Act
        $inventoryOrm = $mapper->fromDomain($inventory);

        // Assert
        self::assertNotNull($inventoryOrm->statusUpdatedAt(), 'settled_at should not be null for IN_PROGRESS status');
    }

    public function testConvertStocksToMilliemesFromDomainToOrm(): void
    {
        // Arrange
        $zoneStorageGateway = $this->createMock(ZoneStorageGatewayInterface::class);
        $mapper = new InventoryMapper($zoneStorageGateway);
        $uuid = ResourceUuid::generate();
        $zoneStorage = new ZoneStorage(ResourceUuid::generate(), NameField::fromString('Zone 1'));
        $date = InventoryDate::fromDateTimeImmutable(ClockFactory::clock()->now());

        $inventory = Inventory::create($uuid, [$zoneStorage], $date);
        $inventory->addItem(
            $this->itemFactory->createWithPreciseStocks(
                theoreticalStock: 12.345,
                realStock: 10.5,
                zoneStorage: $zoneStorage->uuid,
            )->build()
        );

        // Act
        $inventoryOrm = $mapper->fromDomain($inventory);

        // Assert
        $items = $inventoryOrm->items();
        self::assertCount(1, $items);
        $itemOrm = $items[0];
        self::assertSame(
            12345,
            $itemOrm->theoreticalStock(),
            'theoreticalStock should be converted to millièmes (12.345 * 1000 = 12345)'
        );
        self::assertSame(
            10500,
            $itemOrm->realStock(),
            'realStock should be converted to millièmes (10.5 * 1000 = 10500)'
        );
    }

    public function testConvertStocksFromMilliemesOrmToDomain(): void
    {
        // Arrange
        $zoneStorageGateway = $this->createMock(ZoneStorageGatewayInterface::class);
        $mapper = new InventoryMapper($zoneStorageGateway);
        $uuid = ResourceUuid::generate();
        $zoneUuid = ResourceUuid::generate();
        $date = ClockFactory::clock()->now();

        $zoneStorage = new ZoneStorage(
            uuid: $zoneUuid,
            name: NameField::fromString('Zone 1'),
        );
        $zoneStorageGateway->expects(self::once())
            ->method('provide')
            ->with($zoneUuid)
            ->willReturn($zoneStorage)
        ;

        $inventoryOrm = new InventoryOrm(
            uuid: $uuid->toString(),
            date: $date,
            zoneStorages: [$zoneUuid->toString()],
            status: InventoryStatusOrm::DRAFT,
            amount: 0,
            createdAt: $date,
            updatedAt: $date,
            items: [],
            statusUpdatedAt: null
        );

        // Add an item with stocks in millièmes
        $itemOrm = new InventoryItem(
            id: null,
            inventory: $inventoryOrm,
            articleId: ResourceUuid::generate()->toString(),
            zoneStorageId: $zoneUuid->toString(),
            price: 1000,
            theoreticalStock: 12345, // 12345 millièmes → 12.345
            realStock: 10500, // 10500 millièmes → 10.5
            amount: 5000
        );
        $inventoryOrm->addItem($itemOrm);

        // Act
        $inventoryDomain = $mapper->toDomain($inventoryOrm);

        // Assert
        $items = $inventoryDomain->items();
        $itemsArray = iterator_to_array($items->toArray());
        self::assertCount(1, $itemsArray);
        $itemDomain = $itemsArray[0];
        self::assertSame(
            12.345,
            $itemDomain->theoreticalStock()->toUnit(),
            'theoreticalStock should be converted from millièmes (12345 / 1000 = 12.345)'
        );
        self::assertSame(
            10.5,
            $itemDomain->realStock()->toUnit(),
            'realStock should be converted from millièmes (10500 / 1000 = 10.5)'
        );
    }

    public function testHandlesNullableSettledAtFromOrmToDomain(): void
    {
        // Arrange
        $zoneStorageGateway = $this->createMock(ZoneStorageGatewayInterface::class);
        $mapper = new InventoryMapper($zoneStorageGateway);
        $uuid = ResourceUuid::generate();
        $zoneUuid = ResourceUuid::generate();
        $date = ClockFactory::clock()->now();

        $zoneStorage = new ZoneStorage(
            uuid: $zoneUuid,
            name: NameField::fromString('Zone 1'),
        );
        $zoneStorageGateway->expects(self::once())
            ->method('provide')
            ->with($zoneUuid)
            ->willReturn($zoneStorage)
        ;

        $inventoryOrm = new InventoryOrm(
            uuid: $uuid->toString(),
            date: $date,
            zoneStorages: [$zoneUuid->toString()],
            status: InventoryStatusOrm::DRAFT,
            amount: 0,
            createdAt: $date,
            updatedAt: $date,
            items: [], // NULL for DRAFT
            statusUpdatedAt: null
        );

        // Act
        $inventoryDomain = $mapper->toDomain($inventoryOrm);

        // Assert
        self::assertNull($inventoryDomain->statusUpdatedAt(), 'settled_at should be null when mapping DRAFT inventory');
    }

    public function testRoundTripConversionPreservesData(): void
    {
        // Arrange
        $zoneStorageGateway = $this->createMock(ZoneStorageGatewayInterface::class);
        $mapper = new InventoryMapper($zoneStorageGateway);
        $uuid = ResourceUuid::generate();
        $zoneStorage = new ZoneStorage(ResourceUuid::generate(), NameField::fromString('Zone 1'));
        $date = InventoryDate::fromDateTimeImmutable(ClockFactory::clock()->now());

        $originalInventory = Inventory::create($uuid, [$zoneStorage], $date);
        $originalInventory->addItem(
            $this->itemFactory->createWithPreciseStocks(
                theoreticalStock: 12.345,
                realStock: 10.5,
                zoneStorage: $zoneStorage->uuid,
            )->build()
        );

        $zoneStorageGateway->expects(self::once())
            ->method('provide')
            ->willReturn($zoneStorage)
        ;

        // Act: Domain → ORM → Domain
        $inventoryOrm = $mapper->fromDomain($originalInventory);
        $reconvertedInventory = $mapper->toDomain($inventoryOrm);

        // Assert: Data should be preserved after round trip
        self::assertEquals($originalInventory->uuid()->toString(), $reconvertedInventory->uuid()->toString());
        self::assertNull($reconvertedInventory->statusUpdatedAt());

        $reconvertedItems = iterator_to_array($reconvertedInventory->items()->toArray());
        self::assertCount(1, $reconvertedItems);
        $reconvertedItem = $reconvertedItems[0];
        self::assertSame(12.345, $reconvertedItem->theoreticalStock()->toUnit());
        self::assertSame(10.5, $reconvertedItem->realStock()->toUnit());
    }
}
