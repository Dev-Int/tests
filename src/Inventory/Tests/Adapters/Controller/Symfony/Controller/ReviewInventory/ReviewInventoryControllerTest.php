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
use Shared\Tests\RedirectsToLoginTestTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
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
    use RedirectsToLoginTestTrait;

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
                $formData["real_stock_{$articleReal->slug()}_consumer_unit"] = '5'; // Different from theoretical
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
                $formData["real_stock_{$articleReal->slug()}_consumer_unit"] = '10';
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
        self::assertResponseRedirects('/inventories/');

        $crawler = $this->client->followRedirect();
        $flash = $crawler->filter('.flash-error');
        self::assertGreaterThan(0, $flash->count(), 'Error flash message should be displayed');
    }

    public function testRouteNameConstantExists(): void
    {
        self::assertTrue(\defined(ReviewInventoryController::class . '::ROUTE_NAME'));
        self::assertSame('inventory_review', ReviewInventoryController::ROUTE_NAME);
    }

    public function testMarkItemAsReviewedSuccessfully(): void
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

        // Record stock with discrepancies
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
                $formData["real_stock_{$articleReal->slug()}_consumer_unit"] = '5';
            }
        }

        $recordUri = \sprintf(self::RECORD_STOCK_URI, $inventoryUuid, $zoneStorageUuid);
        $this->client->request(Request::METHOD_POST, $recordUri, $formData);
        $this->client->followRedirect();

        // Finish counting
        $finishUri = \sprintf(self::FINISH_COUNTING_URI, $inventoryUuid);
        $this->client->request(Request::METHOD_POST, $finishUri);
        $this->client->followRedirect();

        // Get review page to access form
        $reviewUri = \sprintf(self::REVIEW_URI, $inventoryUuid);
        $crawler = $this->client->request(Request::METHOD_GET, $reviewUri);

        // Select the form and find checkboxes
        $form = $crawler->selectButton($translator->trans('inventory.review.mark_as_reviewed'))->form();

        // Get the first checkbox value to select
        $checkboxes = $crawler->filter('input.item-checkbox');
        self::assertGreaterThan(0, $checkboxes->count(), 'Should have at least one checkbox');

        // Extract checkbox values
        $checkboxValues = $checkboxes->extract(['value']);
        self::assertNotEmpty($checkboxValues);

        // Submit form with one item selected - pass values directly
        $form = $crawler->selectButton($translator->trans('inventory.review.mark_as_reviewed'))->form([
            'review_discrepancies[reviewed_items]' => [$checkboxValues[0]],
        ]);
        $this->client->submit($form);

        // PRG pattern: should always redirect after a successful POST
        self::assertResponseRedirects();
        $crawler = $this->client->followRedirect();

        // The item should now be marked as reviewed (badge badge-success)
        $reviewedBadges = $crawler->filter('.badge.badge-success');
        self::assertGreaterThan(0, $reviewedBadges->count(), 'At least one item should be marked as reviewed');

        // Flash success should be displayed
        $flashSuccess = $crawler->filter('.flash-success');
        self::assertGreaterThan(0, $flashSuccess->count(), 'Should display success flash message');
    }

    public function testMarkAllItemsRedirectsToInventoryList(): void
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

        // Record stock with discrepancies
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
                $formData["real_stock_{$articleReal->slug()}_consumer_unit"] = '5';
            }
        }

        $recordUri = \sprintf(self::RECORD_STOCK_URI, $inventoryUuid, $zoneStorageUuid);
        $this->client->request(Request::METHOD_POST, $recordUri, $formData);
        $this->client->followRedirect();

        // Finish counting
        $finishUri = \sprintf(self::FINISH_COUNTING_URI, $inventoryUuid);
        $this->client->request(Request::METHOD_POST, $finishUri);
        $this->client->followRedirect();

        // Get review page
        $reviewUri = \sprintf(self::REVIEW_URI, $inventoryUuid);
        $crawler = $this->client->request(Request::METHOD_GET, $reviewUri);

        // Get all checkbox values
        $checkboxes = $crawler->filter('input.item-checkbox');
        $checkboxValues = $checkboxes->extract(['value']);
        self::assertNotEmpty($checkboxValues);

        // Submit form with all items selected
        $form = $crawler->selectButton($translator->trans('inventory.review.mark_as_reviewed'))->form([
            'review_discrepancies[reviewed_items]' => $checkboxValues,
        ]);
        $this->client->submit($form);

        // Assert - should redirect to inventory list when all items reviewed
        self::assertResponseRedirects('/inventories/');
    }

    public function testNoItemSelectedShowsWarning(): void
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

        // Record stock with discrepancies
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
                $formData["real_stock_{$articleReal->slug()}_consumer_unit"] = '5';
            }
        }

        $recordUri = \sprintf(self::RECORD_STOCK_URI, $inventoryUuid, $zoneStorageUuid);
        $this->client->request(Request::METHOD_POST, $recordUri, $formData);
        $this->client->followRedirect();

        // Finish counting
        $finishUri = \sprintf(self::FINISH_COUNTING_URI, $inventoryUuid);
        $this->client->request(Request::METHOD_POST, $finishUri);
        $this->client->followRedirect();

        // Get review page
        $reviewUri = \sprintf(self::REVIEW_URI, $inventoryUuid);
        $crawler = $this->client->request(Request::METHOD_GET, $reviewUri);

        // Submit form without selecting any item
        $form = $crawler->selectButton($translator->trans('inventory.review.mark_as_reviewed'))->form();
        $this->client->submit($form);

        // Assert - should redirect with warning flash (translated from UseCase exception)
        self::assertResponseRedirects();
        $crawler = $this->client->followRedirect();

        $flashWarning = $crawler->filter('.flash-warning');
        self::assertGreaterThan(0, $flashWarning->count(), 'Should display warning flash message');
    }

    public function testFormHasCsrfProtection(): void
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

        // Start inventory
        $startUri = \sprintf(self::START_INVENTORY_URI, $inventoryUuid);
        $this->client->request(Request::METHOD_POST, $startUri);
        $this->client->followRedirect();

        // Record stock with discrepancies
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
                $formData["real_stock_{$articleReal->slug()}_consumer_unit"] = '5';
            }
        }

        $recordUri = \sprintf(self::RECORD_STOCK_URI, $inventoryUuid, $zoneStorageUuid);
        $this->client->request(Request::METHOD_POST, $recordUri, $formData);
        $this->client->followRedirect();

        // Finish counting
        $finishUri = \sprintf(self::FINISH_COUNTING_URI, $inventoryUuid);
        $this->client->request(Request::METHOD_POST, $finishUri);
        $this->client->followRedirect();

        // Get review page
        $reviewUri = \sprintf(self::REVIEW_URI, $inventoryUuid);
        $crawler = $this->client->request(Request::METHOD_GET, $reviewUri);

        // Assert - form should have CSRF token
        $csrfToken = $crawler->filter('input[name="review_discrepancies[_token]"]');
        self::assertCount(1, $csrfToken, 'Form should have CSRF token');
    }

    public function testFormRejectsInvalidCsrfToken(): void
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

        // Start inventory
        $startUri = \sprintf(self::START_INVENTORY_URI, $inventoryUuid);
        $this->client->request(Request::METHOD_POST, $startUri);
        $this->client->followRedirect();

        // Record stock with discrepancies
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
                $formData["real_stock_{$articleReal->slug()}_consumer_unit"] = '5';
            }
        }

        $recordUri = \sprintf(self::RECORD_STOCK_URI, $inventoryUuid, $zoneStorageUuid);
        $this->client->request(Request::METHOD_POST, $recordUri, $formData);
        $this->client->followRedirect();

        $finishUri = \sprintf(self::FINISH_COUNTING_URI, $inventoryUuid);
        $this->client->request(Request::METHOD_POST, $finishUri);
        $this->client->followRedirect();

        // Get review page to find item values
        $reviewUri = \sprintf(self::REVIEW_URI, $inventoryUuid);
        $crawler = $this->client->request(Request::METHOD_GET, $reviewUri);

        // Get all checkbox values (simulating "select all")
        $checkboxes = $crawler->filter('input.item-checkbox');
        $checkboxValues = $checkboxes->extract(['value']);
        self::assertNotEmpty($checkboxValues, 'Should have at least one checkbox');

        // Act - Submit form with all items selected but invalid CSRF token
        $this->client->request(Request::METHOD_POST, $reviewUri, [
            'review_discrepancies' => [
                'reviewed_items' => $checkboxValues,
                '_token' => 'invalid_csrf_token',
            ],
        ]);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $crawler = $this->client->request(Request::METHOD_GET, $reviewUri);
        $reviewedBadges = $crawler->filter('.badge.badge-success');
        self::assertCount(0, $reviewedBadges, 'Aucun item ne devrait être marqué comme révisé avec un CSRF invalide');
    }

    public function testAccessDeniedForRoleUser(): void
    {
        $this->logoutUser();
        $this->authenticateAsRoleUser();
        $this->client->catchExceptions(false);
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Access denied. Required role: ROLE_INVENTORY_MANAGER');
        $this->client->request(Request::METHOD_GET, $this->getProtectedUri());
    }

    protected function getProtectedUri(): string
    {
        // UUID factice, access_control vérifie l'auth avant le routage complet
        return \sprintf(self::REVIEW_URI, '00000000-0000-0000-0000-000000000000');
    }
}
