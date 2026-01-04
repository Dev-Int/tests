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

namespace Inventory\Tests\Adapters\Controller\Symfony\Controller\GetInventories;

use Admin\Tests\Factory\ZoneStorageFactory;
use Inventory\Adapters\Controller\Symfony\Controller\GetInventories\GetInventoriesController;
use Inventory\Adapters\Gateway\ORM\Entity\InventoryStatus;
use Inventory\Tests\Factory\InventoryFactory;
use Inventory\Tests\Story\InventoryStory;
use Shared\Adapters\Exception\ApplicationNotReady;
use Shared\Adapters\Gateway\Pagination\Pagination;
use Shared\Entities\Clock\ClockFactory;
use Shared\Tests\BaseFunctionalTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 *
 * @covers \Inventory\Adapters\Controller\Symfony\Controller\GetInventories\GetInventoriesController
 */
final class GetInventoriesControllerTest extends BaseFunctionalTestCase
{
    use Factories;

    private const string GET_INVENTORIES_URI = '/inventories';

    public function testGetInventoriesDisplaysListWhenConfigured(): void
    {
        // Arrange - InventoryStory charge toute la config Admin + Articles
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');
        InventoryStory::load();

        // Act
        $this->client->request(Request::METHOD_GET, self::GET_INVENTORIES_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('inventory.titlePage'));
    }

    public function testGetInventoriesRedirectsToConfigurationWhenNoArticles(): void
    {
        // Arrange - Base vide = pas d'articles configurés

        // Act
        $this->client->request(Request::METHOD_GET, self::GET_INVENTORIES_URI);

        // Assert - Doit rediriger vers /admin/configure
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/configure');

        // Suivre la redirection et vérifier le flash
        $crawler = $this->client->followRedirect();
        $flash = $crawler->filter('.flash-error')->text();

        self::assertSame(ApplicationNotReady::MESSAGE, $flash);
    }

    public function testGetInventoriesRouteNameConstantExists(): void
    {
        // Assert
        self::assertTrue(\defined(GetInventoriesController::class . '::ROUTE_NAME'));
        self::assertSame('inventory_index', GetInventoriesController::ROUTE_NAME);
    }

    public function testGetInventoriesDisplaysExistingInventoriesWithData(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');
        InventoryStory::load();
        $now = ClockFactory::clock()->now();
        $futureDate = $now->modify('+1 day');
        $zoneStorages = ZoneStorageFactory::all();

        InventoryFactory::createOne([
            'date' => $futureDate,
            'zoneStorages' => [$zoneStorages[0]->_real()->uuid()],
            'status' => InventoryStatus::DRAFT->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => $now,
        ]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::GET_INVENTORIES_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('inventory.titlePage'));

        // Verify inventory is displayed with date and status
        // Note: We filter out inventory_create and inventory_paginated turbo-frames
        $inventoryRows = $crawler->filter('turbo-frame[id^="inventory_"]:not(#inventory_create):not(#inventory_paginated)');
        self::assertCount(1, $inventoryRows);
        self::assertStringContainsString($futureDate->format('Y-m-d'), $inventoryRows->text());
        self::assertStringContainsString('draft', $inventoryRows->text());
    }

    public function testGetInventoriesDisplaysTableStructureWhenNoInventoriesExist(): void
    {
        // Arrange - Config OK but no inventories
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');
        InventoryStory::load();

        // Act
        $this->client->request(Request::METHOD_GET, self::GET_INVENTORIES_URI);

        // Assert - Page loads with table structure
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('ul.table');
        // Verify table headers are present
        self::assertSelectorTextContains('.head', $translator->trans('inventory.form.date.label'));
        self::assertSelectorTextContains('.head', $translator->trans('inventory.status.label'));
    }

    public function testGetInventoriesDisplaysCreateButton(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');
        InventoryStory::load();

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::GET_INVENTORIES_URI);

        // Assert
        self::assertResponseIsSuccessful();
        $createButton = $crawler->filter('a[href="/inventories/create"]');
        self::assertCount(1, $createButton);
        self::assertStringContainsString($translator->trans('inventory.create.titleShort'), $createButton->text());
    }

    public function testGetInventoriesDisplaysCorrectStatusForDraft(): void
    {
        // Arrange
        InventoryStory::load();
        $now = ClockFactory::clock()->now();
        $zoneStorages = ZoneStorageFactory::all();

        InventoryFactory::createOne([
            'date' => $now->modify('+1 day'),
            'zoneStorages' => [$zoneStorages[0]->_real()->uuid()],
            'status' => InventoryStatus::DRAFT->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => $now,
        ]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::GET_INVENTORIES_URI);

        // Assert
        self::assertResponseIsSuccessful();
        $inventoryRow = $crawler->filter('turbo-frame[id^="inventory_"]:not(#inventory_create):not(#inventory_paginated)');
        self::assertStringContainsString('draft', $inventoryRow->text());
    }

    public function testGetInventoriesDisplaysStartButtonForDraftStatus(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');
        InventoryStory::load();
        $now = ClockFactory::clock()->now();
        $zoneStorages = ZoneStorageFactory::all();

        InventoryFactory::createOne([
            'date' => $now->modify('+1 day'),
            'zoneStorages' => [$zoneStorages[0]->_real()->uuid()],
            'status' => InventoryStatus::DRAFT->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => $now,
        ]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::GET_INVENTORIES_URI);

        // Assert
        self::assertResponseIsSuccessful();
        $startButton = $crawler->filter('button[type="submit"]');
        self::assertGreaterThan(0, $startButton->count());
        self::assertStringContainsString($translator->trans('inventory.start.button'), $startButton->text());
    }

    public function testGetInventoriesDisplaysCancelButtonForCancellableStatus(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');
        InventoryStory::load();
        $now = ClockFactory::clock()->now();
        $zoneStorages = ZoneStorageFactory::all();

        InventoryFactory::createOne([
            'date' => $now->modify('+1 day'),
            'zoneStorages' => [$zoneStorages[0]->_real()->uuid()],
            'status' => InventoryStatus::DRAFT->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => $now,
        ]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::GET_INVENTORIES_URI);

        // Assert
        self::assertResponseIsSuccessful();
        $cancelButton = $crawler->filter('button.btn-secondary');
        self::assertGreaterThan(0, $cancelButton->count());
        self::assertStringContainsString($translator->trans('inventory.cancel.button'), $cancelButton->text());
    }

    public function testGetInventoriesDisplaysZoneStorageLabelsInList(): void
    {
        // Arrange
        InventoryStory::load();
        $now = ClockFactory::clock()->now();
        $zoneStorages = ZoneStorageFactory::all();
        $zoneLabel = $zoneStorages[0]->label();

        InventoryFactory::createOne([
            'date' => $now->modify('+1 day'),
            'zoneStorages' => [$zoneStorages[0]->_real()->uuid()],
            'status' => InventoryStatus::DRAFT->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => $now,
        ]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::GET_INVENTORIES_URI);

        // Assert
        self::assertResponseIsSuccessful();
        $zonesList = $crawler->filter('.zones-list');
        self::assertStringContainsString($zoneLabel, $zonesList->text());
    }

    public function testGetInventoriesPageTitleAndHeadingAreCorrect(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');
        InventoryStory::load();

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::GET_INVENTORIES_URI);

        // Assert
        self::assertResponseIsSuccessful();

        // Check page title
        $pageTitle = $crawler->filter('title')->text();
        self::assertStringContainsString($translator->trans('inventory.titlePage'), $pageTitle);

        // Check H1 heading
        self::assertSelectorTextContains('h1', $translator->trans('inventory.titlePage'));
    }

    public function testGetInventoriesWithMultipleInventoriesDisplaysAll(): void
    {
        // Arrange
        InventoryStory::load();
        $now = ClockFactory::clock()->now();
        $zoneStorages = ZoneStorageFactory::all();
        $zoneUuid = $zoneStorages[0]->_real()->uuid();

        // Create 3 inventories with different dates
        InventoryFactory::createOne([
            'date' => $now->modify('+1 day'),
            'zoneStorages' => [$zoneUuid],
            'status' => InventoryStatus::DRAFT->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => $now,
        ]);
        InventoryFactory::createOne([
            'date' => $now->modify('+2 days'),
            'zoneStorages' => [$zoneUuid],
            'status' => InventoryStatus::DRAFT->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => $now,
        ]);
        InventoryFactory::createOne([
            'date' => $now->modify('+3 days'),
            'zoneStorages' => [$zoneUuid],
            'status' => InventoryStatus::DRAFT->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => $now,
        ]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::GET_INVENTORIES_URI);

        // Assert
        self::assertResponseIsSuccessful();
        $inventoryRows = $crawler->filter('turbo-frame[id^="inventory_"]:not(#inventory_create):not(#inventory_paginated)');
        self::assertCount(3, $inventoryRows);
    }

    public function testGetInventoriesDisplaysBackToHomeButton(): void
    {
        // Arrange
        InventoryStory::load();

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::GET_INVENTORIES_URI);

        // Assert
        self::assertResponseIsSuccessful();
        $backButton = $crawler->filter('a[href="/"]');
        self::assertGreaterThan(0, $backButton->count());
    }

    public function testGetInventoriesPaginatedDisplaysOnlyFirstPage(): void
    {
        // Arrange
        InventoryStory::load();
        $now = ClockFactory::clock()->now();
        $zoneStorages = ZoneStorageFactory::all();
        $zoneUuid = $zoneStorages[0]->_real()->uuid();

        // Create 30 inventories to force pagination (25 per page)
        for ($i = 0; $i < 30; $i++) {
            InventoryFactory::createOne([
                'date' => $now->modify("+{$i} days"),
                'zoneStorages' => [$zoneUuid],
                'status' => InventoryStatus::DRAFT->value,
                'createdAt' => $now,
                'updatedAt' => $now,
                'statusUpdatedAt' => $now,
            ]);
        }

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::GET_INVENTORIES_URI);

        // Assert
        self::assertResponseIsSuccessful();
        $inventoryRows = $crawler->filter('turbo-frame[id^="inventory_"]:not(#inventory_create):not(#inventory_paginated)');
        self::assertCount(Pagination::DEFAULT_ITEMS_PER_PAGE, $inventoryRows);
    }

    public function testGetInventoriesSecondPageDisplaysRemainingItems(): void
    {
        // Arrange
        InventoryStory::load();
        $now = ClockFactory::clock()->now();
        $zoneStorages = ZoneStorageFactory::all();
        $zoneUuid = $zoneStorages[0]->_real()->uuid();

        // Create 30 inventories
        for ($i = 0; $i < 30; $i++) {
            InventoryFactory::createOne([
                'date' => $now->modify("+{$i} days"),
                'zoneStorages' => [$zoneUuid],
                'status' => InventoryStatus::DRAFT->value,
                'createdAt' => $now,
                'updatedAt' => $now,
                'statusUpdatedAt' => $now,
            ]);
        }

        // Act - Request page 2
        $crawler = $this->client->request(
            Request::METHOD_GET,
            self::GET_INVENTORIES_URI . '?page=2&itemsPerPage=25'
        );

        // Assert - Should show remaining 5 items (30 - 25 = 5)
        self::assertResponseIsSuccessful();
        $inventoryRows = $crawler->filter('turbo-frame[id^="inventory_"]:not(#inventory_create):not(#inventory_paginated)');
        self::assertCount(5, $inventoryRows);
    }

    public function testGetInventoriesPaginationComponentNotDisplayedWhenFewItems(): void
    {
        // Arrange
        InventoryStory::load();
        $now = ClockFactory::clock()->now();
        $zoneStorages = ZoneStorageFactory::all();
        $zoneUuid = $zoneStorages[0]->_real()->uuid();

        // Create only 5 inventories (less than 25)
        for ($i = 0; $i < 5; $i++) {
            InventoryFactory::createOne([
                'date' => $now->modify("+{$i} days"),
                'zoneStorages' => [$zoneUuid],
                'status' => InventoryStatus::DRAFT->value,
                'createdAt' => $now,
                'updatedAt' => $now,
                'statusUpdatedAt' => $now,
            ]);
        }

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::GET_INVENTORIES_URI);

        // Assert - Pagination nav should not be displayed (only 1 page)
        self::assertResponseIsSuccessful();
        $inventoryRows = $crawler->filter('turbo-frame[id^="inventory_"]:not(#inventory_create):not(#inventory_paginated)');
        self::assertCount(5, $inventoryRows);

        // Pagination component exists but nav is hidden (totalPages <= 1)
        $paginationNav = $crawler->filter('#pagination nav');
        self::assertCount(0, $paginationNav);
    }
}
