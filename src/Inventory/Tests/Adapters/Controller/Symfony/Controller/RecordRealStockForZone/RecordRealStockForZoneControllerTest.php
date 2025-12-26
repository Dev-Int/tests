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
use Inventory\Tests\Factory\InventoryFactory;
use Inventory\Tests\Story\InventoryStory;
use Shared\Entities\Clock\ClockFactory;
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

        // Create a DRAFT inventory
        $inventory = InventoryFactory::createOne([
            'date' => $futureDate,
            'zoneStorages' => [$zoneStorageUuid],
            'status' => InventoryStatus::DRAFT->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => null,
        ]);

        $inventoryUuid = $inventory->_real()->uuid();

        // Start the inventory to load articles as items
        $startUri = \sprintf(self::START_INVENTORY_URI, $inventoryUuid);
        $this->client->request(Request::METHOD_POST, $startUri);
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->client->followRedirect(); // Consume start success flash

        // Act - GET the record stock form
        $uri = \sprintf(self::RECORD_STOCK_URI, $inventoryUuid, $zoneStorageUuid);
        $crawler = $this->client->request(Request::METHOD_GET, $uri);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        // Check page title contains zone name
        self::assertSelectorTextContains('h1', 'Réserve positive');

        // Check that articles are displayed in the form
        $articleInputs = $crawler->filter('input[type="number"]');
        self::assertGreaterThan(0, $articleInputs->count(), 'Form should contain article stock inputs');
    }

    public function testPostRecordStockSuccess(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');
        InventoryStory::load();

        $now = ClockFactory::clock()->now();
        $futureDate = $now->modify('+1 day');

        $zonePositive = ZoneStorageFactory::findBy(['label' => 'Réserve positive'])[0];
        $zoneStorageUuid = $zonePositive->_real()->uuid();

        // Create a DRAFT inventory
        $inventory = InventoryFactory::createOne([
            'date' => $futureDate,
            'zoneStorages' => [$zoneStorageUuid],
            'status' => InventoryStatus::DRAFT->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => null,
        ]);

        $inventoryUuid = $inventory->_real()->uuid();

        // Start the inventory to load articles as items
        $startUri = \sprintf(self::START_INVENTORY_URI, $inventoryUuid);
        $this->client->request(Request::METHOD_POST, $startUri);
        $this->client->followRedirect(); // Consume start success flash

        // Get the "Lait" article UUID (created in InventoryStory for Réserve positive)
        $articles = ArticleFactory::all();
        $laitArticle = null;
        foreach ($articles as $a) {
            if ($a->_real()->name() === 'Lait') {
                $laitArticle = $a;

                break;
            }
        }
        self::assertNotNull($laitArticle, 'Article "Lait" should exist');
        $articleUuid = $laitArticle->_real()->uuid();

        // Act - POST to record stock
        $uri = \sprintf(self::RECORD_STOCK_URI, $inventoryUuid, $zoneStorageUuid);
        $this->client->request(Request::METHOD_POST, $uri, [
            "real_stock_{$articleUuid}" => '15.5',
        ]);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/inventories');

        $crawler = $this->client->followRedirect();
        $flash = $crawler->filter('.flash-success')->text();
        self::assertSame($translator->trans('inventory.zone.record.success'), $flash);
    }

    public function testRecordStockFailsOnNonInProgressInventory(): void
    {
        // Arrange
        InventoryStory::load();

        $now = ClockFactory::clock()->now();
        $zonePositive = ZoneStorageFactory::findBy(['label' => 'Réserve positive'])[0];
        $zoneStorageUuid = $zonePositive->_real()->uuid();

        // Create a DRAFT inventory (NOT in_progress)
        $inventory = InventoryFactory::createOne([
            'date' => $now->modify('+1 day'),
            'zoneStorages' => [$zoneStorageUuid],
            'status' => InventoryStatus::DRAFT->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => null,
        ]);

        $inventoryUuid = $inventory->_real()->uuid();

        // Act - Try to GET record stock form on DRAFT inventory
        $uri = \sprintf(self::RECORD_STOCK_URI, $inventoryUuid, $zoneStorageUuid);
        $this->client->request(Request::METHOD_GET, $uri);

        // Assert - Should display form but with no items (since inventory wasn't started)
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        // The form should show "no articles" message
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

        // Assert - Should redirect with error
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
