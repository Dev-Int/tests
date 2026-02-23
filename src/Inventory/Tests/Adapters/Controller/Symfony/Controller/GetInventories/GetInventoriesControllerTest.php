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
use Shared\Tests\RedirectsToLoginTestTrait;
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
    use RedirectsToLoginTestTrait;

    private const string GET_INVENTORIES_URI = '/inventories/';

    public function testGetInventoriesDisplaysListWhenConfigured(): void
    {
        // Arrange
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
        // Arrange && Act
        $this->client->request(Request::METHOD_GET, self::GET_INVENTORIES_URI);

        // Assert
        self::assertResponseRedirects(
            expectedLocation: '/admin/configure',
            expectedCode: Response::HTTP_FOUND,
            message: 'Redirection attendue vers /admin/configure'
        );

        $crawler = $this->client->followRedirect();
        $flash = $crawler->filter('.flash-error')->text();

        self::assertSame(ApplicationNotReady::MESSAGE, $flash, 'Message flash attendu pour application non configurée');
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
        $inventoryRows = $crawler->filter(
            'turbo-frame[id^="inventory_"]:not(#inventory_create):not(#inventory_paginated)'
        );
        self::assertCount(1, $inventoryRows);
        self::assertStringContainsString($futureDate->format('Y-m-d'), $inventoryRows->text());
        self::assertStringContainsString($translator->trans('inventory.status.draft'), $inventoryRows->text());
    }

    public function testGetInventoriesDisplaysTableStructureWhenNoInventoriesExist(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');
        InventoryStory::load();

        // Act
        $this->client->request(Request::METHOD_GET, self::GET_INVENTORIES_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('ul.table');

        self::assertSelectorTextContains(
            '.head',
            $translator->trans('inventory.form.date.label'),
            'Le tableau devrait avoir un en-tête date'
        );
        self::assertSelectorTextContains(
            '.head',
            $translator->trans('inventory.status.label'),
            'Le tableau devrait avoir un en-tête statut'
        );
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
        $inventoryRow = $crawler->filter(
            'turbo-frame[id^="inventory_"]:not(#inventory_create):not(#inventory_paginated)'
        );
        self::assertStringContainsString($translator->trans('inventory.status.draft'), $inventoryRow->text());
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
        $startButton = $crawler->filter('.actions-group button[type="submit"]');
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
        $inventoryRows = $crawler->filter(
            'turbo-frame[id^="inventory_"]:not(#inventory_create):not(#inventory_paginated)'
        );
        self::assertCount(3, $inventoryRows, '3 inventaires attendus sur la première page');
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
        $inventoryRows = $crawler->filter(
            'turbo-frame[id^="inventory_"]:not(#inventory_create):not(#inventory_paginated)'
        );
        self::assertCount(Pagination::DEFAULT_ITEMS_PER_PAGE, $inventoryRows, '25 inventaires attendus sur la première page');
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

        // Assert
        self::assertResponseIsSuccessful();
        $inventoryRows = $crawler->filter(
            'turbo-frame[id^="inventory_"]:not(#inventory_create):not(#inventory_paginated)'
        );
        self::assertCount(5, $inventoryRows, '5 inventaires attendus sur la page 2');
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

        // Assert
        self::assertResponseIsSuccessful();
        $inventoryRows = $crawler->filter(
            'turbo-frame[id^="inventory_"]:not(#inventory_create):not(#inventory_paginated)'
        );
        self::assertCount(5, $inventoryRows, '5 inventaires attendus sur la première page');

        $paginationNav = $crawler->filter('#pagination nav');
        self::assertCount(0, $paginationNav, 'La navigation de pagination devrait être masquée avec 1 seule page');
    }

    public function testGetInventoriesWithStatusFilterShowsOnlyMatchingStatus(): void
    {
        // Arrange
        InventoryStory::load();
        $now = ClockFactory::clock()->now();
        $zoneStorages = ZoneStorageFactory::all();
        $zoneUuid = $zoneStorages[0]->_real()->uuid();

        // Create inventories with different statuses
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
            'status' => InventoryStatus::COMPLETED->value,
            'createdAt' => $now->modify('+1 second'),
            'updatedAt' => $now->modify('+1 second'),
            'statusUpdatedAt' => $now->modify('+1 second'),
        ]);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            self::GET_INVENTORIES_URI . '?status=draft'
        );

        // Assert
        self::assertResponseIsSuccessful();
        $inventoryRows = $crawler->filter(
            'turbo-frame[id^="inventory_"]:not(#inventory_create):not(#inventory_paginated)'
        );
        self::assertCount(1, $inventoryRows, '1 inventaire brouillon attendu');
    }

    public function testGetInventoriesWithDateAfterFilterShowsInventoriesAfterDate(): void
    {
        // Arrange
        InventoryStory::load();
        $now = ClockFactory::clock()->now();
        $zoneStorages = ZoneStorageFactory::all();
        $zoneUuid = $zoneStorages[0]->_real()->uuid();

        // Create inventories with different dates
        InventoryFactory::createOne([
            'date' => $now->modify('-10 days'),
            'zoneStorages' => [$zoneUuid],
            'status' => InventoryStatus::DRAFT->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => $now,
        ]);
        InventoryFactory::createOne([
            'date' => $now->modify('+10 days'),
            'zoneStorages' => [$zoneUuid],
            'status' => InventoryStatus::DRAFT->value,
            'createdAt' => $now->modify('+1 second'),
            'updatedAt' => $now->modify('+1 second'),
            'statusUpdatedAt' => $now->modify('+1 second'),
        ]);

        // Act
        $afterDate = $now->format('Y-m-d');
        $crawler = $this->client->request(
            Request::METHOD_GET,
            self::GET_INVENTORIES_URI . '?date[after]=' . $afterDate
        );

        // Assert
        self::assertResponseIsSuccessful();
        $inventoryRows = $crawler->filter(
            'turbo-frame[id^="inventory_"]:not(#inventory_create):not(#inventory_paginated)'
        );
        self::assertCount(1, $inventoryRows, '1 inventaire attendu après aujourd\'hui');
    }

    public function testGetInventoriesWithDateBeforeFilterShowsInventoriesBeforeDate(): void
    {
        // Arrange
        InventoryStory::load();
        $now = ClockFactory::clock()->now();
        $zoneStorages = ZoneStorageFactory::all();
        $zoneUuid = $zoneStorages[0]->_real()->uuid();

        // Create inventories with different dates
        InventoryFactory::createOne([
            'date' => $now->modify('-10 days'),
            'zoneStorages' => [$zoneUuid],
            'status' => InventoryStatus::DRAFT->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => $now,
        ]);
        InventoryFactory::createOne([
            'date' => $now->modify('+10 days'),
            'zoneStorages' => [$zoneUuid],
            'status' => InventoryStatus::DRAFT->value,
            'createdAt' => $now->modify('+1 second'),
            'updatedAt' => $now->modify('+1 second'),
            'statusUpdatedAt' => $now->modify('+1 second'),
        ]);

        // Act
        $beforeDate = $now->format('Y-m-d');
        $crawler = $this->client->request(
            Request::METHOD_GET,
            self::GET_INVENTORIES_URI . '?date[before]=' . $beforeDate
        );

        // Assert
        self::assertResponseIsSuccessful();
        $inventoryRows = $crawler->filter(
            'turbo-frame[id^="inventory_"]:not(#inventory_create):not(#inventory_paginated)'
        );
        self::assertCount(1, $inventoryRows, '1 inventaire attendu avant aujourd\'hui');
    }

    public function testGetInventoriesWithZoneStorageFilterShowsMatchingInventories(): void
    {
        // Arrange
        InventoryStory::load();
        $now = ClockFactory::clock()->now();
        $zoneStorages = ZoneStorageFactory::all();

        // Need at least 2 zone storages
        if (\count($zoneStorages) < 2) {
            self::markTestSkipped('Need at least 2 zone storages for this test');
        }

        $zoneUuid1 = $zoneStorages[0]->_real()->uuid();
        $zoneUuid2 = $zoneStorages[1]->_real()->uuid();

        // Create inventory with zone 1
        InventoryFactory::createOne([
            'date' => $now->modify('+1 day'),
            'zoneStorages' => [$zoneUuid1],
            'status' => InventoryStatus::DRAFT->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => $now,
        ]);
        // Create inventory with zone 2
        InventoryFactory::createOne([
            'date' => $now->modify('+2 days'),
            'zoneStorages' => [$zoneUuid2],
            'status' => InventoryStatus::DRAFT->value,
            'createdAt' => $now->modify('+1 second'),
            'updatedAt' => $now->modify('+1 second'),
            'statusUpdatedAt' => $now->modify('+1 second'),
        ]);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            self::GET_INVENTORIES_URI . '?zoneStorage=' . $zoneUuid1
        );

        // Assert
        self::assertResponseIsSuccessful();
        $inventoryRows = $crawler->filter(
            'turbo-frame[id^="inventory_"]:not(#inventory_create):not(#inventory_paginated)'
        );
        self::assertCount(1, $inventoryRows, '1 inventaire attendu avec la zone 1');
    }

    public function testGetInventoriesWithCombinedFiltersWork(): void
    {
        // Arrange
        InventoryStory::load();
        $now = ClockFactory::clock()->now();
        $zoneStorages = ZoneStorageFactory::all();
        $zoneUuid = $zoneStorages[0]->_real()->uuid();

        InventoryFactory::createOne([
            'date' => $now->modify('+5 days'),
            'zoneStorages' => [$zoneUuid],
            'status' => InventoryStatus::DRAFT->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => $now,
        ]);
        InventoryFactory::createOne([
            'date' => $now->modify('+5 days'),
            'zoneStorages' => [$zoneUuid],
            'status' => InventoryStatus::COMPLETED->value,
            'createdAt' => $now->modify('+1 second'),
            'updatedAt' => $now->modify('+1 second'),
            'statusUpdatedAt' => $now->modify('+1 second'),
        ]);
        InventoryFactory::createOne([
            'date' => $now->modify('-5 days'),
            'zoneStorages' => [$zoneUuid],
            'status' => InventoryStatus::DRAFT->value,
            'createdAt' => $now->modify('+2 seconds'),
            'updatedAt' => $now->modify('+2 seconds'),
            'statusUpdatedAt' => $now->modify('+2 seconds'),
        ]);

        // Act
        $afterDate = $now->format('Y-m-d');
        $crawler = $this->client->request(
            Request::METHOD_GET,
            self::GET_INVENTORIES_URI . '?status=draft&date[after]=' . $afterDate
        );

        // Assert
        self::assertResponseIsSuccessful();
        $inventoryRows = $crawler->filter(
            'turbo-frame[id^="inventory_"]:not(#inventory_create):not(#inventory_paginated)'
        );
        self::assertCount(1, $inventoryRows, 'Seul l\'inventaire brouillon futur devrait être affiché');
    }

    public function testGetInventoriesWithInvalidDateFilterShowsError(): void
    {
        // Arrange
        InventoryStory::load();
        $now = ClockFactory::clock()->now();
        $zoneStorages = ZoneStorageFactory::all();
        $zoneUuid = $zoneStorages[0]->_real()->uuid();

        InventoryFactory::createOne([
            'date' => $now->modify('+1 day'),
            'zoneStorages' => [$zoneUuid],
            'status' => InventoryStatus::DRAFT->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => $now,
        ]);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            self::GET_INVENTORIES_URI . '?date[after]=invalid-date'
        );

        // Assert
        self::assertResponseIsSuccessful();

        $errorMessages = $crawler->filter('.form-error, .invalid-feedback, [class*="error"]');
        self::assertGreaterThan(0, $errorMessages->count(), 'Message d\'erreur attendu pour date invalide');

        $inventoryRows = $crawler->filter(
            'turbo-frame[id^="inventory_"]:not(#inventory_create):not(#inventory_paginated)'
        );
        self::assertCount(0, $inventoryRows, 'Aucun inventaire ne devrait être affiché si la validation a échoué');
    }

    public function testGetInventoriesFilterFormIsDisplayed(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');
        InventoryStory::load();

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::GET_INVENTORIES_URI);

        self::assertResponseIsSuccessful();
        $filterSection = $crawler->filter('details.filters-section');
        self::assertCount(1, $filterSection, 'Section de filtres attendue');
        self::assertStringContainsString(
            $translator->trans('inventory.filter.title'),
            $filterSection->text(),
            'La section de filtres devrait contenir le titre'
        );
    }

    public function testGetInventoriesPaginationWorksWithFilters(): void
    {
        // Arrange
        InventoryStory::load();
        $now = ClockFactory::clock()->now();
        $zoneStorages = ZoneStorageFactory::all();
        $zoneUuid = $zoneStorages[0]->_real()->uuid();

        for ($i = 0; $i < 30; $i++) {
            InventoryFactory::createOne([
                'date' => $now->modify("+{$i} days"),
                'zoneStorages' => [$zoneUuid],
                'status' => InventoryStatus::DRAFT->value,
                'createdAt' => $now->modify("+{$i} seconds"),
                'updatedAt' => $now->modify("+{$i} seconds"),
                'statusUpdatedAt' => $now->modify("+{$i} seconds"),
            ]);
        }

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            self::GET_INVENTORIES_URI . '?status=draft&page=2'
        );

        // Assert
        self::assertResponseIsSuccessful();
        $inventoryRows = $crawler->filter(
            'turbo-frame[id^="inventory_"]:not(#inventory_create):not(#inventory_paginated)'
        );
        self::assertCount(5, $inventoryRows, '5 inventaires attendus sur la page 2 avec filtre de statut');
    }

    protected function getProtectedUri(): string
    {
        return self::GET_INVENTORIES_URI;
    }
}
