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

namespace Inventory\Tests\Adapters\Controller\Symfony\Controller\RecordRealStockForZone;

use Admin\Tests\Factory\ArticleFactory;
use Admin\Tests\Factory\ZoneStorageFactory;
use Inventory\Adapters\Controller\Symfony\Controller\RecordRealStockForZone\RecordRealStockForZoneController;
use Inventory\Adapters\Gateway\ORM\Entity\InventoryStatus;
use Inventory\Entities\Repository\InventoryRepository;
use Inventory\Tests\Factory\InventoryFactory;
use Inventory\Tests\Story\InventoryStory;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\ResourceUuid;
use Shared\Tests\BaseFunctionalTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 *
 * @covers \Inventory\Adapters\Controller\Symfony\Controller\RecordRealStockForZone\RecordRealStockForZoneController
 */
final class RecordRealStockForZoneControllerTest extends BaseFunctionalTestCase
{
    use Factories;

    public const string RECORD_STOCK_URI = '/inventories/%s/zones/%s/record';
    public const string START_INVENTORY_URI = '/inventories/%s/start';

    public function testGetRecordStockFormDisplaysArticles(): void
    {
        // Arrange
        InventoryStory::load();

        $now = ClockFactory::clock()->now();
        $futureDate = $now->modify('+1 day');

        $zonePositive = ZoneStorageFactory::findBy(['label' => 'Réserve positive'])[0];
        $zoneStorageUuid = $zonePositive->_real()->uuid();

        $inventory = InventoryFactory::createOne([
            'date' => $futureDate,
            'zoneStorages' => [$zoneStorageUuid],
            'status' => InventoryStatus::DRAFT->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => null,
        ]);

        $inventoryUuid = $inventory->_real()->uuid();

        $startUri = \sprintf(self::START_INVENTORY_URI, $inventoryUuid);
        $this->client->request(Request::METHOD_POST, $startUri);
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->client->followRedirect();

        // Act
        $uri = \sprintf(self::RECORD_STOCK_URI, $inventoryUuid, $zoneStorageUuid);
        $crawler = $this->client->request(Request::METHOD_GET, $uri);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertSelectorTextContains('h1', 'Réserve positive');

        $articleInputs = $crawler->filter('input[type="number"]');
        self::assertGreaterThan(0, $articleInputs->count(), 'Form should contain article stock inputs');
    }

    public function testPostRecordStockSuccess(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        /** @var InventoryRepository $repository */
        $repository = self::getContainer()->get(InventoryRepository::class);
        InventoryStory::load();

        $now = ClockFactory::clock()->now();
        $futureDate = $now->modify('+1 day');

        $zonePositive = ZoneStorageFactory::findBy(['label' => 'Réserve positive'])[0];
        $zoneStorageUuid = $zonePositive->_real()->uuid();

        $inventory = InventoryFactory::createOne([
            'date' => $futureDate,
            'zoneStorages' => [$zoneStorageUuid],
            'status' => InventoryStatus::DRAFT->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => null,
        ]);

        $inventoryUuid = $inventory->_real()->uuid();

        $startUri = \sprintf(self::START_INVENTORY_URI, $inventoryUuid);
        $this->client->request(Request::METHOD_POST, $startUri);
        $this->client->followRedirect();

        $articles = ArticleFactory::all();
        $laitArticle = null;
        $camembertArticle = null;
        foreach ($articles as $article) {
            if ($article->_real()->name() === 'Lait') {
                $laitArticle = $article;
            }
            if ($article->_real()->name() === 'Camembert') {
                $camembertArticle = $article;
            }
        }
        self::assertNotNull($laitArticle, 'Article "Lait" should exist');
        self::assertNotNull($camembertArticle, 'Article "Camembert" should exist');
        $laitSlug = $laitArticle->_real()->slug();
        $camembertSlug = $camembertArticle->_real()->slug();

        // Act - Use multi-level input format (consumer_unit level - base unit)
        $uri = \sprintf(self::RECORD_STOCK_URI, $inventoryUuid, $zoneStorageUuid);
        $this->client->request(Request::METHOD_POST, $uri, [
            "real_stock_{$laitSlug}_consumer_unit" => '15.5',
            "real_stock_{$camembertSlug}_consumer_unit" => '8',
        ]);

        // Assert HTTP response
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/inventories');

        $crawler = $this->client->followRedirect();
        $flash = $crawler->filter('.flash-success')->text();
        self::assertSame($translator->trans('inventory.zone.record.success'), $flash);

        // Assert
        $updatedInventory = $repository->getByUuid(ResourceUuid::fromString($inventoryUuid));
        $zoneStorageUuidVo = ResourceUuid::fromString($zoneStorageUuid);
        $laitUuid = ResourceUuid::fromString($laitArticle->_real()->uuid());
        $camembertUuid = ResourceUuid::fromString($camembertArticle->_real()->uuid());

        $laitItem = $updatedInventory->items()->findByArticleAndZone($laitUuid, $zoneStorageUuidVo);
        $camembertItem = $updatedInventory->items()->findByArticleAndZone($camembertUuid, $zoneStorageUuidVo);

        self::assertNotNull($laitItem, 'Lait item should exist in inventory');
        self::assertNotNull($camembertItem, 'Camembert item should exist in inventory');

        // Lait: 15.5 L entered (packaging 1L = 1 unit) → realStock = 15500 milliemes
        self::assertSame(15500, $laitItem->realStock()->toMilliemes(), 'Lait real stock should be 15.5L (15500 milliemes)');

        // Camembert: 8 pieces entered (packaging 1 Pce = 1 unit) → realStock = 8000 milliemes
        self::assertSame(8000, $camembertItem->realStock()->toMilliemes(), 'Camembert real stock should be 8 pieces (8000 milliemes)');
    }

    public function testPostRecordStockWithZeroValue(): void
    {
        // Arrange
        /** @var InventoryRepository $repository */
        $repository = self::getContainer()->get(InventoryRepository::class);
        InventoryStory::load();

        $now = ClockFactory::clock()->now();
        $futureDate = $now->modify('+1 day');

        $zonePositive = ZoneStorageFactory::findBy(['label' => 'Réserve positive'])[0];
        $zoneStorageUuid = $zonePositive->_real()->uuid();

        $inventory = InventoryFactory::createOne([
            'date' => $futureDate,
            'zoneStorages' => [$zoneStorageUuid],
            'status' => InventoryStatus::DRAFT->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => null,
        ]);

        $inventoryUuid = $inventory->_real()->uuid();

        $startUri = \sprintf(self::START_INVENTORY_URI, $inventoryUuid);
        $this->client->request(Request::METHOD_POST, $startUri);
        $this->client->followRedirect();

        $articles = ArticleFactory::all();
        $laitArticle = null;
        $camembertArticle = null;
        foreach ($articles as $article) {
            if ($article->_real()->name() === 'Lait') {
                $laitArticle = $article;
            }
            if ($article->_real()->name() === 'Camembert') {
                $camembertArticle = $article;
            }
        }
        self::assertNotNull($laitArticle, 'Article "Lait" should exist');
        self::assertNotNull($camembertArticle, 'Article "Camembert" should exist');
        $laitSlug = $laitArticle->_real()->slug();
        $camembertSlug = $camembertArticle->_real()->slug();

        // Act - Enter explicit zero for Camembert (should be recorded, not skipped)
        $uri = \sprintf(self::RECORD_STOCK_URI, $inventoryUuid, $zoneStorageUuid);
        $this->client->request(Request::METHOD_POST, $uri, [
            "real_stock_{$laitSlug}_consumer_unit" => '10',
            "real_stock_{$camembertSlug}_consumer_unit" => '0', // Explicit zero
        ]);

        // Assert HTTP response
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);

        // Assert
        $updatedInventory = $repository->getByUuid(ResourceUuid::fromString($inventoryUuid));
        $zoneStorageUuidVo = ResourceUuid::fromString($zoneStorageUuid);
        $laitUuid = ResourceUuid::fromString($laitArticle->_real()->uuid());
        $camembertUuid = ResourceUuid::fromString($camembertArticle->_real()->uuid());

        $laitItem = $updatedInventory->items()->findByArticleAndZone($laitUuid, $zoneStorageUuidVo);
        $camembertItem = $updatedInventory->items()->findByArticleAndZone($camembertUuid, $zoneStorageUuidVo);

        self::assertNotNull($laitItem, 'Lait item should exist in inventory');
        self::assertNotNull($camembertItem, 'Camembert item should exist in inventory');

        // Lait: 10 L entered → realStock = 10000 milliemes
        self::assertSame(10000, $laitItem->realStock()->toMilliemes(), 'Lait real stock should be 10L');

        // Camembert: explicit 0 entered → realStock = 0 milliemes (not skipped!)
        self::assertSame(0, $camembertItem->realStock()->toMilliemes(), 'Camembert explicit zero should be recorded');
    }

    public function testPostRecordStockFailsOnEmptyFields(): void
    {
        // Arrange
        InventoryStory::load();

        $now = ClockFactory::clock()->now();
        $futureDate = $now->modify('+1 day');

        $zonePositive = ZoneStorageFactory::findBy(['label' => 'Réserve positive'])[0];
        $zoneStorageUuid = $zonePositive->_real()->uuid();

        $inventory = InventoryFactory::createOne([
            'date' => $futureDate,
            'zoneStorages' => [$zoneStorageUuid],
            'status' => InventoryStatus::DRAFT->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => null,
        ]);

        $inventoryUuid = $inventory->_real()->uuid();

        $startUri = \sprintf(self::START_INVENTORY_URI, $inventoryUuid);
        $this->client->request(Request::METHOD_POST, $startUri);
        $this->client->followRedirect();

        $articles = ArticleFactory::all();
        $laitArticle = null;
        foreach ($articles as $article) {
            if ($article->_real()->name() === 'Lait') {
                $laitArticle = $article;
            }
        }
        self::assertNotNull($laitArticle, 'Article "Lait" should exist');
        $laitSlug = $laitArticle->_real()->slug();

        // Act - Only fill Lait, leave Camembert empty (not submitted)
        $uri = \sprintf(self::RECORD_STOCK_URI, $inventoryUuid, $zoneStorageUuid);
        $this->client->request(Request::METHOD_POST, $uri, [
            "real_stock_{$laitSlug}_consumer_unit" => '5',
            // Camembert consumer_unit field not submitted → should trigger validation error
        ]);

        // Assert - Should redirect back to form with error
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $crawler = $this->client->followRedirect();
        $flash = $crawler->filter('.flash-error')->text();

        // Error message should mention missing article (Camembert)
        self::assertStringContainsString('Camembert', $flash, 'Error should mention missing article');
    }

    public function testRecordStockFailsOnNonInProgressInventory(): void
    {
        // Arrange
        InventoryStory::load();

        $now = ClockFactory::clock()->now();
        $zonePositive = ZoneStorageFactory::findBy(['label' => 'Réserve positive'])[0];
        $zoneStorageUuid = $zonePositive->_real()->uuid();

        $inventory = InventoryFactory::createOne([
            'date' => $now->modify('+1 day'),
            'zoneStorages' => [$zoneStorageUuid],
            'status' => InventoryStatus::DRAFT->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => null,
        ]);

        $inventoryUuid = $inventory->_real()->uuid();

        // Act
        $uri = \sprintf(self::RECORD_STOCK_URI, $inventoryUuid, $zoneStorageUuid);
        $this->client->request(Request::METHOD_GET, $uri);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $crawler = $this->client->getCrawler();
        $rows = $crawler->filter('tbody tr');
        self::assertCount(1, $rows); // Only the "no articles" row
    }

    public function testRecordStockFailsOnInventoryNotFound(): void
    {
        // Arrange
        InventoryStory::load();

        $zonePositive = ZoneStorageFactory::findBy(['label' => 'Réserve positive'])[0];
        $zoneStorageUuid = $zonePositive->_real()->uuid();

        $nonExistentInventoryUuid = '00000000-0000-0000-0000-000000000000';

        // Act
        $uri = \sprintf(self::RECORD_STOCK_URI, $nonExistentInventoryUuid, $zoneStorageUuid);
        $this->client->request(Request::METHOD_GET, $uri);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/inventories');

        $crawler = $this->client->followRedirect();
        $flash = $crawler->filter('.flash-error');
        self::assertGreaterThan(0, $flash->count(), 'Error flash message should be displayed');
    }

    public function testRouteNameConstantExists(): void
    {
        self::assertTrue(\defined(RecordRealStockForZoneController::class . '::ROUTE_NAME'));
        self::assertSame('inventory_zone_record_stock', RecordRealStockForZoneController::ROUTE_NAME);
    }
}
