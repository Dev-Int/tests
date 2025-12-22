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

namespace Inventory\Tests\UseCases\LoadArticlesIntoInventory;

use App\Inventory\Tests\Factory\InventoryFakerFactory;
use Inventory\Entities\Exception\CannotLoadArticlesOnNonDraftInventory;
use Inventory\Entities\Exception\InventoryNotFound;
use Inventory\Entities\InventoryItem;
use Inventory\Entities\Repository\InventoryRepository;
use Inventory\Entities\VO\Article;
use Inventory\Entities\VO\ZoneStorage;
use Inventory\UseCases\Gateway\ArticleGateway;
use Inventory\UseCases\LoadArticlesIntoInventory\LoadArticlesIntoInventory;
use Inventory\UseCases\LoadArticlesIntoInventory\LoadArticlesIntoInventoryRequest;
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
 * @covers \Inventory\UseCases\LoadArticlesIntoInventory\LoadArticlesIntoInventory
 */
final class LoadArticlesIntoInventoryTest extends TestCase
{
    protected function setUp(): void
    {
        ClockFactory::initialize(new FrozenClock(new \DateTimeImmutable('2025-12-01')));
    }

    public function testLoadArticlesSuccessfully(): void
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
            name: NameField::fromString('Yaourt'),
            unitPrice: Amount::fromCents(1000),
            quantity: Quantity::fromUnit(5.0),
            slug: 'yaourt'
        );

        $repository = $this->createMock(InventoryRepository::class);
        $articleGateway = $this->createMock(ArticleGateway::class);
        $request = $this->createMock(LoadArticlesIntoInventoryRequest::class);

        $request->expects(self::once())
            ->method('inventoryUuid')
            ->willReturn($inventoryUuid)
        ;
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
        $repository->expects(self::once())->method('save');

        // Act
        $useCase = new LoadArticlesIntoInventory($repository, $articleGateway);
        $response = $useCase->execute($request);

        // Assert
        self::assertSame(expected: 1, actual: $response->itemsLoaded);
        $items = iterator_to_array($response->inventory->items()->toArray());
        self::assertCount(expectedCount: 1, haystack: $items);
        self::assertSame($article->uuid->toString(), $items[0]->article()->toString());
        self::assertSame(expected: 1000, actual: $items[0]->price()->toInt());
        self::assertSame(expected: 5000, actual: $items[0]->theoreticalStock()->toMilliemes());
        self::assertSame(expected: 0, actual: $items[0]->realStock()->toMilliemes());
        // amount = 10.00€ × 5 = 50.00€ = 5000 cents
        self::assertSame(expected: 5000, actual: $items[0]->amount()->toInt());
    }

    public function testLoadArticlesReplacesExistingItems(): void
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

        $existingArticle = new Article(
            uuid: ResourceUuid::generate(),
            name: NameField::fromString('Yaourt'),
            unitPrice: Amount::fromCents(500),
            quantity: Quantity::fromUnit(2.0),
            slug: 'yaourt'
        );
        $inventory->addItem(InventoryItem::createFromArticle($existingArticle));

        $newArticle = new Article(
            uuid: ResourceUuid::generate(),
            name: NameField::fromString('Yaourt'),
            unitPrice: Amount::fromCents(1500),
            quantity: Quantity::fromUnit(3.0),
            slug: 'yaourt'
        );

        $repository = $this->createMock(InventoryRepository::class);
        $articleGateway = $this->createMock(ArticleGateway::class);
        $request = $this->createMock(LoadArticlesIntoInventoryRequest::class);

        $request->expects(self::once())
            ->method('inventoryUuid')
            ->willReturn($inventoryUuid)
        ;
        $repository->expects(self::once())
            ->method('getByUuid')
            ->with($inventoryUuid)
            ->willReturn($inventory)
        ;
        $articleGateway->expects(self::once())
            ->method('provideForZones')
            ->willReturn([$newArticle])
        ;
        $repository->expects(self::once())->method('save');

        // Act
        $useCase = new LoadArticlesIntoInventory($repository, $articleGateway);
        $response = $useCase->execute($request);

        // Assert - only the new article should be present (old one replaced)
        self::assertSame(expected: 1, actual: $response->itemsLoaded);
        $items = iterator_to_array($response->inventory->items()->toArray());
        self::assertCount(expectedCount: 1, haystack: $items);
        self::assertSame($newArticle->uuid->toString(), $items[0]->article()->toString());
    }

    public function testLoadArticlesFromMultipleZones(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $zoneStorage1 = new ZoneStorage(
            uuid: ResourceUuid::generate(),
            name: NameField::fromString('Zone 1'),
        );
        $zoneStorage2 = new ZoneStorage(
            uuid: ResourceUuid::generate(),
            name: NameField::fromString('Zone 2'),
        );
        $inventory = (new InventoryFakerFactory())->createDraft()
            ->withUuid($inventoryUuid)
            ->withZoneStorages([$zoneStorage1, $zoneStorage2])
            ->build()
        ;

        $article1 = new Article(
            uuid: ResourceUuid::generate(),
            name: NameField::fromString('Yaourt'),
            unitPrice: Amount::fromCents(1000),
            quantity: Quantity::fromUnit(5.0),
            slug: 'yaourt'
        );
        $article2 = new Article(
            uuid: ResourceUuid::generate(),
            name: NameField::fromString('Article 2'),
            unitPrice: Amount::fromCents(2000),
            quantity: Quantity::fromUnit(10.0),
            slug: 'article-2'
        );

        $repository = $this->createMock(InventoryRepository::class);
        $articleGateway = $this->createMock(ArticleGateway::class);
        $request = $this->createMock(LoadArticlesIntoInventoryRequest::class);

        $request->expects(self::once())
            ->method('inventoryUuid')
            ->willReturn($inventoryUuid)
        ;
        $repository->expects(self::once())
            ->method('getByUuid')
            ->with($inventoryUuid)
            ->willReturn($inventory)
        ;
        $articleGateway->expects(self::once())
            ->method('provideForZones')
            ->with([$zoneStorage1->uuid, $zoneStorage2->uuid])
            ->willReturn([$article1, $article2])
        ;
        $repository->expects(self::once())->method('save');

        // Act
        $useCase = new LoadArticlesIntoInventory($repository, $articleGateway);
        $response = $useCase->execute($request);

        // Assert
        self::assertSame(expected: 2, actual: $response->itemsLoaded);
        $items = iterator_to_array($response->inventory->items()->toArray());
        self::assertCount(expectedCount: 2, haystack: $items);
    }

    public function testLoadArticlesFailsWhenInventoryNotDraft(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();
        $zoneStorage = new ZoneStorage(
            uuid: ResourceUuid::generate(),
            name: NameField::fromString('Zone 1'),
        );
        $inventory = (new InventoryFakerFactory())->createInProgress()
            ->withUuid($inventoryUuid)
            ->withZoneStorages([$zoneStorage])
            ->build()
        ;

        $repository = $this->createMock(InventoryRepository::class);
        $articleGateway = $this->createMock(ArticleGateway::class);
        $request = $this->createMock(LoadArticlesIntoInventoryRequest::class);

        $request->expects(self::once())
            ->method('inventoryUuid')
            ->willReturn($inventoryUuid)
        ;
        $repository->expects(self::once())
            ->method('getByUuid')
            ->with($inventoryUuid)
            ->willReturn($inventory)
        ;
        $articleGateway->expects(self::never())->method('provideForZones');
        $repository->expects(self::never())->method('save');

        $this->expectException(CannotLoadArticlesOnNonDraftInventory::class);
        $this->expectExceptionMessage(CannotLoadArticlesOnNonDraftInventory::MESSAGE);

        // Act
        $useCase = new LoadArticlesIntoInventory($repository, $articleGateway);
        $useCase->execute($request);
    }

    public function testLoadArticlesFailsWhenInventoryNotFound(): void
    {
        // Arrange
        $inventoryUuid = ResourceUuid::generate();

        $repository = $this->createMock(InventoryRepository::class);
        $articleGateway = $this->createMock(ArticleGateway::class);
        $request = $this->createMock(LoadArticlesIntoInventoryRequest::class);

        $request->expects(self::once())
            ->method('inventoryUuid')
            ->willReturn($inventoryUuid)
        ;
        $repository->expects(self::once())
            ->method('getByUuid')
            ->with($inventoryUuid)
            ->willThrowException(new InventoryNotFound($inventoryUuid))
        ;
        $articleGateway->expects(self::never())->method('provideForZones');
        $repository->expects(self::never())->method('save');

        $this->expectException(InventoryNotFound::class);
        $this->expectExceptionMessage(InventoryNotFound::MESSAGE);

        // Act
        $useCase = new LoadArticlesIntoInventory($repository, $articleGateway);
        $useCase->execute($request);
    }
}
