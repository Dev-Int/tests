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

namespace Inventory\Tests\Adapters\Controller\Symfony\Controller\FinishCounting;

use Admin\Tests\Factory\ArticleFactory;
use Admin\Tests\Factory\ZoneStorageFactory;
use Inventory\Adapters\Controller\Symfony\Controller\FinishCounting\FinishCountingController;
use Inventory\Adapters\Controller\Symfony\Controller\ReviewInventory\ReviewInventoryController;
use Inventory\Adapters\Gateway\ORM\Entity\InventoryStatus as ORMInventoryStatus;
use Inventory\Entities\Exception\IncompleteInventoryCounting;
use Inventory\Entities\Exception\InvalidStatusTransition;
use Inventory\Entities\Repository\InventoryRepository;
use Inventory\Entities\VO\InventoryStatus;
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
 * @covers \Inventory\Adapters\Controller\Symfony\Controller\FinishCounting\FinishCountingController
 */
final class FinishCountingControllerTest extends BaseFunctionalTestCase
{
    use Factories;

    public const string FINISH_COUNTING_URI = '/inventories/%s/finish-counting';
    public const string START_INVENTORY_URI = '/inventories/%s/start';
    public const string RECORD_STOCK_URI = '/inventories/%s/zones/%s/record';

    public function testFinishCountingSuccessRedirectsToReview(): void
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
            'status' => ORMInventoryStatus::DRAFT->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => null,
        ]);

        $inventoryUuid = $inventory->_real()->uuid();

        // Start the inventory to load articles
        $startUri = \sprintf(self::START_INVENTORY_URI, $inventoryUuid);
        $this->client->request(Request::METHOD_POST, $startUri);
        $this->client->followRedirect();

        // Record stock for all articles in the zone (Lait and Camembert are in positive zone)
        $articles = ArticleFactory::all();
        $formData = [];
        foreach ($articles as $article) {
            $articleReal = $article->_real();
            $articleZones = $articleReal->zoneStorages();
            $zoneUuids = [];
            foreach ($articleZones as $zone) {
                $zoneUuids[] = $zone->uuid();
            }
            // Only count articles in the positive zone
            if (\in_array($zoneStorageUuid, $zoneUuids, true)) {
                $formData["real_stock_{$articleReal->slug()}_parcel"] = '10';
            }
        }

        $recordUri = \sprintf(self::RECORD_STOCK_URI, $inventoryUuid, $zoneStorageUuid);
        $this->client->request(Request::METHOD_POST, $recordUri, $formData);
        $this->client->followRedirect();

        // Act - Finish counting
        $finishUri = \sprintf(self::FINISH_COUNTING_URI, $inventoryUuid);
        $this->client->request(Request::METHOD_POST, $finishUri);

        // Assert - Should redirect to review page
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects(\sprintf('/inventories/%s/review', $inventoryUuid));

        $crawler = $this->client->followRedirect();
        $flash = $crawler->filter('.flash-success')->text();
        self::assertSame($translator->trans('inventory.finish_counting.success'), $flash);

        // Assert - Status should be REVIEW
        $updatedInventory = $repository->getByUuid(ResourceUuid::fromString($inventoryUuid));
        self::assertTrue($updatedInventory->status()->equals(InventoryStatus::REVIEW));
    }

    public function testFinishCountingFailsOnNonInProgressInventory(): void
    {
        // Arrange
        InventoryStory::load();

        $now = ClockFactory::clock()->now();
        $zonePositive = ZoneStorageFactory::findBy(['label' => 'Réserve positive'])[0];

        $inventory = InventoryFactory::createOne([
            'date' => $now->modify('+1 day'),
            'zoneStorages' => [$zonePositive->_real()->uuid()],
            'status' => ORMInventoryStatus::DRAFT->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => null,
        ]);

        $inventoryUuid = $inventory->_real()->uuid();

        // Act - Try to finish counting on DRAFT inventory
        $uri = \sprintf(self::FINISH_COUNTING_URI, $inventoryUuid);
        $this->client->request(Request::METHOD_POST, $uri);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/inventories');

        $crawler = $this->client->followRedirect();
        $flash = $crawler->filter('.flash-error')->text();
        self::assertSame(InvalidStatusTransition::MESSAGE, $flash);
    }

    public function testFinishCountingFailsOnIncompleteItems(): void
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
            'status' => ORMInventoryStatus::DRAFT->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => null,
        ]);

        $inventoryUuid = $inventory->_real()->uuid();

        // Start inventory but DON'T record any stock
        $startUri = \sprintf(self::START_INVENTORY_URI, $inventoryUuid);
        $this->client->request(Request::METHOD_POST, $startUri);
        $this->client->followRedirect();

        // Act - Try to finish counting without counting items
        $finishUri = \sprintf(self::FINISH_COUNTING_URI, $inventoryUuid);
        $this->client->request(Request::METHOD_POST, $finishUri);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/inventories');

        $crawler = $this->client->followRedirect();
        $flash = $crawler->filter('.flash-error')->text();
        self::assertSame(IncompleteInventoryCounting::MESSAGE, $flash);
    }

    public function testFinishCountingFailsOnInventoryNotFound(): void
    {
        // Arrange
        InventoryStory::load();

        $nonExistentInventoryUuid = '00000000-0000-0000-0000-000000000000';

        // Act
        $uri = \sprintf(self::FINISH_COUNTING_URI, $nonExistentInventoryUuid);
        $this->client->request(Request::METHOD_POST, $uri);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/inventories');

        $crawler = $this->client->followRedirect();
        $flash = $crawler->filter('.flash-error');
        self::assertGreaterThan(0, $flash->count(), 'Error flash message should be displayed');
    }

    public function testRouteNameConstantExists(): void
    {
        self::assertTrue(\defined(FinishCountingController::class . '::ROUTE_NAME'));
        self::assertSame('inventory_finish_counting', FinishCountingController::ROUTE_NAME);
    }

    public function testReviewRouteNameConstantExists(): void
    {
        self::assertTrue(\defined(ReviewInventoryController::class . '::ROUTE_NAME'));
        self::assertSame('inventory_review', ReviewInventoryController::ROUTE_NAME);
    }
}
