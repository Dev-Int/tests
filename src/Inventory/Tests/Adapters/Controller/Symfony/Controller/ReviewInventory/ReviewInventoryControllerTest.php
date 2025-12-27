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

namespace Inventory\Tests\Adapters\Controller\Symfony\Controller\ReviewInventory;

use Admin\Tests\Factory\ArticleFactory;
use Admin\Tests\Factory\ZoneStorageFactory;
use Inventory\Adapters\Controller\Symfony\Controller\ReviewInventory\ReviewInventoryController;
use Inventory\Adapters\Gateway\ORM\Entity\InventoryStatus as ORMInventoryStatus;
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
 * @covers \Inventory\Adapters\Controller\Symfony\Controller\ReviewInventory\ReviewInventoryController
 */
final class ReviewInventoryControllerTest extends BaseFunctionalTestCase
{
    use Factories;

    public const string REVIEW_URI = '/inventories/%s/review';
    public const string START_INVENTORY_URI = '/inventories/%s/start';
    public const string RECORD_STOCK_URI = '/inventories/%s/zones/%s/record';
    public const string FINISH_COUNTING_URI = '/inventories/%s/finish-counting';

    public function testReviewPageDisplaysDiscrepancies(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');
        InventoryStory::load();

        $now = ClockFactory::clock()->now();
        $futureDate = $now->modify('+1 day');

        $zonePositive = ZoneStorageFactory::findBy(['label' => 'Réserve positive'])[0];
        $zoneStorageUuid = $zonePositive->_real()->uuid();

        $inventory = InventoryFactory::createOne([
            'date' => $futureDate,
            'zoneStorages' => [$zoneStorageUuid],
            'status' => ORMInventoryStatus::DRAFT->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => null,
        ]);

        $inventoryUuid = $inventory->_real()->uuid();

        // Start inventory
        $startUri = \sprintf(self::START_INVENTORY_URI, $inventoryUuid);
        $this->client->request(Request::METHOD_POST, $startUri);
        $this->client->followRedirect();

        // Record stock with different values (to create discrepancies)
        $articles = ArticleFactory::all();
        $formData = [];
        foreach ($articles as $article) {
            $articleReal = $article->_real();
            $articleZones = $articleReal->zoneStorages();
            $zoneUuids = [];
            foreach ($articleZones as $zone) {
                $zoneUuids[] = $zone->uuid();
            }
            if (\in_array($zoneStorageUuid, $zoneUuids, true)) {
                // Use different values to create discrepancy
                $formData["real_stock_{$articleReal->slug()}_parcel"] = '5'; // Different from theoretical
            }
        }

        $recordUri = \sprintf(self::RECORD_STOCK_URI, $inventoryUuid, $zoneStorageUuid);
        $this->client->request(Request::METHOD_POST, $recordUri, $formData);
        $this->client->followRedirect();

        // Finish counting
        $finishUri = \sprintf(self::FINISH_COUNTING_URI, $inventoryUuid);
        $this->client->request(Request::METHOD_POST, $finishUri);
        $this->client->followRedirect();

        // Act - Visit review page directly
        $reviewUri = \sprintf(self::REVIEW_URI, $inventoryUuid);
        $crawler = $this->client->request(Request::METHOD_GET, $reviewUri);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('inventory.review.titlePage'));

        // Should display discrepancies
        $table = $crawler->filter('table.table');
        self::assertGreaterThan(0, $table->count(), 'Should display discrepancies table');
    }

    public function testReviewPageIsAccessibleAndDisplaysSummary(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');
        InventoryStory::load();

        $now = ClockFactory::clock()->now();
        $futureDate = $now->modify('+1 day');

        $zonePositive = ZoneStorageFactory::findBy(['label' => 'Réserve positive'])[0];
        $zoneStorageUuid = $zonePositive->_real()->uuid();

        $inventory = InventoryFactory::createOne([
            'date' => $futureDate,
            'zoneStorages' => [$zoneStorageUuid],
            'status' => ORMInventoryStatus::DRAFT->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => null,
        ]);

        $inventoryUuid = $inventory->_real()->uuid();

        // Start inventory
        $startUri = \sprintf(self::START_INVENTORY_URI, $inventoryUuid);
        $this->client->request(Request::METHOD_POST, $startUri);
        $this->client->followRedirect();

        // Record stock
        $articles = ArticleFactory::all();
        $formData = [];
        foreach ($articles as $article) {
            $articleReal = $article->_real();
            $articleZones = $articleReal->zoneStorages();
            $zoneUuids = [];
            foreach ($articleZones as $zone) {
                $zoneUuids[] = $zone->uuid();
            }
            if (\in_array($zoneStorageUuid, $zoneUuids, true)) {
                $formData["real_stock_{$articleReal->slug()}_parcel"] = '10';
            }
        }

        $recordUri = \sprintf(self::RECORD_STOCK_URI, $inventoryUuid, $zoneStorageUuid);
        $this->client->request(Request::METHOD_POST, $recordUri, $formData);
        $this->client->followRedirect();

        // Finish counting
        $finishUri = \sprintf(self::FINISH_COUNTING_URI, $inventoryUuid);
        $this->client->request(Request::METHOD_POST, $finishUri);
        $this->client->followRedirect();

        // Act - Access review page
        $reviewUri = \sprintf(self::REVIEW_URI, $inventoryUuid);
        $crawler = $this->client->request(Request::METHOD_GET, $reviewUri);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('inventory.review.titlePage'));

        // Should display summary alert (either success or warning)
        $alert = $crawler->filter('.alert');
        self::assertGreaterThan(0, $alert->count(), 'Should display summary alert');
        self::assertStringContainsString($translator->trans('inventory.review.summary'), $alert->text());
    }

    public function testReviewPageFailsOnInventoryNotFound(): void
    {
        // Arrange
        InventoryStory::load();

        $nonExistentInventoryUuid = '00000000-0000-0000-0000-000000000000';

        // Act
        $uri = \sprintf(self::REVIEW_URI, $nonExistentInventoryUuid);
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
        self::assertTrue(\defined(ReviewInventoryController::class . '::ROUTE_NAME'));
        self::assertSame('inventory_review', ReviewInventoryController::ROUTE_NAME);
    }
}
