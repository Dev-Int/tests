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

namespace Inventory\Tests\UseCases\LoadArticlesAndStartInventory;

use Inventory\Entities\Exception\CannotLoadArticlesOnNonDraftInventory;
use Inventory\Entities\Exception\InventoryNotFound;
use Inventory\Entities\Exception\NoArticlesToLoad;
use Inventory\Entities\Repository\InventoryRepository;
use Inventory\Entities\VO\Article;
use Inventory\Entities\VO\InventoryStatus;
use Inventory\Entities\VO\ZoneStorage;
use Inventory\Tests\DataBuilder\InventoryItemDataBuilder;
use Inventory\Tests\Factory\InventoryFakerFactory;
use Inventory\UseCases\Gateway\ArticleGatewayInterface;
use Inventory\UseCases\LoadArticlesAndStartInventory\LoadArticlesAndStartInventory;
use Inventory\UseCases\LoadArticlesAndStartInventory\LoadArticlesAndStartInventoryRequest;
use PHPUnit\Framework\TestCase;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\Clock\FrozenClock;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\Amount;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\Quantity;

/**
 * @group unitTest
 *
 * @covers \Inventory\UseCases\LoadArticlesAndStartInventory\LoadArticlesAndStartInventory
 */
final class StartInventoryTest extends TestCase
{
    protected function setUp(): void
    {
        ClockFactory::initialize(new FrozenClock(new \DateTimeImmutable('2025-12-01')));
    }

    public function testStartInventoryLoadsArticlesAndChangesStatus(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $zoneStorage = new ZoneStorage(
            uuid: ResourceUuid::generate(),
            name: NameField::fromString('Zone 1'),
        );
        $inventory = (new InventoryFakerFactory())->createDraft()
            ->withUuid($inventoryUuid)
            ->withZoneStorages([$zoneStorage])
            ->build()
        ;

        $article = new Article(
            uuid: ResourceUuid::generate(),
            zoneStorageUuid: $zoneStorage->uuid,
            name: NameField::fromString('Yaourt'),
            unitPrice: Amount::fromCents(1000),
            quantity: Quantity::fromUnit(5.0),
            slug: 'yaourt',
            packaging: InventoryItemDataBuilder::defaultPackaging(),
        );

        $repository = $this->createMock(InventoryRepository::class);
        $articleGateway = $this->createMock(ArticleGatewayInterface::class);
        $useCase = new LoadArticlesAndStartInventory($repository, $articleGateway);
        $request = $this->createMock(LoadArticlesAndStartInventoryRequest::class);

        // Assert
        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);

        $repository->expects(self::once())
            ->method('getByUuid')
            ->with($inventoryUuid)
            ->willReturn($inventory)
        ;
        $articleGateway->expects(self::once())
            ->method('provideForZones')
            ->with([$zoneStorage->uuid])
            ->willReturn([$article])
        ;
        $repository->expects(self::once())->method('start');

        // Act
        $response = $useCase->execute($request);

        // Assert
        self::assertTrue($response->inventory->status()->equals(InventoryStatus::IN_PROGRESS));
        $items = iterator_to_array($response->inventory->items()->toArray());
        self::assertCount(1, $items);
        self::assertNotNull($response->inventory->statusUpdatedAt());
    }

    public function testStartInventoryThrowsIfInventoryNotFound(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();

        $repository = $this->createMock(InventoryRepository::class);
        $articleGateway = $this->createMock(ArticleGatewayInterface::class);
        $useCase = new LoadArticlesAndStartInventory($repository, $articleGateway);
        $request = $this->createMock(LoadArticlesAndStartInventoryRequest::class);

        // Assert
        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);

        $repository->expects(self::once())
            ->method('getByUuid')
            ->with($inventoryUuid)
            ->willThrowException(new InventoryNotFound($inventoryUuid))
        ;
        $articleGateway->expects(self::never())->method('provideForZones');
        $repository->expects(self::never())->method('start');

        $this->expectException(InventoryNotFound::class);

        // Act
        $useCase->execute($request);
    }

    public function testStartInventoryThrowsIfInventoryNotDraft(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();

        $repository = $this->createMock(InventoryRepository::class);
        $articleGateway = $this->createMock(ArticleGatewayInterface::class);
        $useCase = new LoadArticlesAndStartInventory($repository, $articleGateway);
        $request = $this->createMock(LoadArticlesAndStartInventoryRequest::class);

        $zoneStorage = new ZoneStorage(
            uuid: ResourceUuid::generate(),
            name: NameField::fromString('Zone 1'),
        );
        $inventory = (new InventoryFakerFactory())->createInProgress()
            ->withUuid($inventoryUuid)
            ->withZoneStorages([$zoneStorage])
            ->build()
        ;

        // Assert
        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);

        $repository->expects(self::once())
            ->method('getByUuid')
            ->with($inventoryUuid)
            ->willReturn($inventory)
        ;
        $articleGateway->expects(self::never())->method('provideForZones');
        $repository->expects(self::never())->method('start');

        $this->expectException(CannotLoadArticlesOnNonDraftInventory::class);
        $this->expectExceptionMessage(CannotLoadArticlesOnNonDraftInventory::MESSAGE);

        // Act
        $useCase->execute($request);
    }

    public function testStartInventoryThrowsIfNoArticlesInZones(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $zoneStorage = new ZoneStorage(
            uuid: ResourceUuid::generate(),
            name: NameField::fromString('Zone vide'),
        );
        $inventory = (new InventoryFakerFactory())->createDraft()
            ->withUuid($inventoryUuid)
            ->withZoneStorages([$zoneStorage])
            ->build()
        ;

        $repository = $this->createMock(InventoryRepository::class);
        $articleGateway = $this->createMock(ArticleGatewayInterface::class);
        $useCase = new LoadArticlesAndStartInventory($repository, $articleGateway);
        $request = $this->createMock(LoadArticlesAndStartInventoryRequest::class);

        // Assert
        $request->expects(self::once())->method('inventoryUuid')->willReturn($inventoryUuid);

        $repository->expects(self::once())
            ->method('getByUuid')
            ->with($inventoryUuid)
            ->willReturn($inventory)
        ;
        $articleGateway->expects(self::once())
            ->method('provideForZones')
            ->with([$zoneStorage->uuid])
            ->willReturn([])
        ;
        $repository->expects(self::never())->method('start');

        $this->expectException(NoArticlesToLoad::class);

        // Act
        $useCase->execute($request);
    }
}
