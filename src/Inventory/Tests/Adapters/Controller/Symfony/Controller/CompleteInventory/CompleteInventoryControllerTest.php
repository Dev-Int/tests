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

namespace Inventory\Tests\Adapters\Controller\Symfony\Controller\CompleteInventory;

use Admin\Tests\Factory\ArticleFactory;
use Admin\Tests\Factory\ZoneStorageFactory;
use Inventory\Adapters\Controller\Symfony\Controller\CompleteInventory\CompleteInventoryController;
use Inventory\Adapters\Gateway\ORM\Entity\InventoryStatus as ORMInventoryStatus;
use Inventory\Entities\Repository\InventoryRepository;
use Inventory\Entities\VO\InventoryStatus;
use Inventory\Tests\Factory\InventoryFactory;
use Inventory\Tests\Story\InventoryStory;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\ResourceUuid;
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
 * @covers \Inventory\Adapters\Controller\Symfony\Controller\CompleteInventory\CompleteInventoryController
 */
final class CompleteInventoryControllerTest extends BaseFunctionalTestCase
{
    use Factories;
    use RedirectsToLoginTestTrait;

    public const string COMPLETE_URI = '/inventories/%s/complete';
    public const string START_URI = '/inventories/%s/start';
    public const string RECORD_STOCK_URI = '/inventories/%s/zones/%s/record';
    public const string FINISH_COUNTING_URI = '/inventories/%s/finish-counting';
    public const string REVIEW_URI = '/inventories/%s/review';

    public function testCompleteInventorySuccessfully(): void
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

        // Start the inventory
        $this->client->request(Request::METHOD_POST, \sprintf(self::START_URI, $inventoryUuid));
        $this->client->followRedirect();

        // Record stock with discrepancy (different from theoretical)
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
                // Record a different stock to create discrepancy
                // consumer_unit is the required field (base unit)
                $formData["real_stock_{$articleReal->slug()}_consumer_unit"] = '5';
            }
        }

        $this->client->request(
            Request::METHOD_POST,
            \sprintf(self::RECORD_STOCK_URI, $inventoryUuid, $zoneStorageUuid),
            $formData
        );
        $this->client->followRedirect();

        // Finish counting to go to REVIEW status
        $this->client->request(Request::METHOD_POST, \sprintf(self::FINISH_COUNTING_URI, $inventoryUuid));
        $this->client->followRedirect();

        // Review all discrepancies
        $crawler = $this->client->request(Request::METHOD_GET, \sprintf(self::REVIEW_URI, $inventoryUuid));
        $checkboxes = $crawler->filter('input.item-checkbox');
        $checkboxValues = $checkboxes->extract(['value']);
        self::assertNotEmpty($checkboxValues, 'Should have at least one checkbox');

        $form = $crawler->selectButton($translator->trans('inventory.review.mark_as_reviewed'))->form([
            'review_discrepancies[reviewed_items]' => $checkboxValues,
        ]);
        $this->client->submit($form);
        $this->client->followRedirect();

        // Act - Complete the inventory
        $this->client->request(Request::METHOD_POST, \sprintf(self::COMPLETE_URI, $inventoryUuid));

        // Assert - Should redirect to an inventory list
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/inventories/');

        $crawler = $this->client->followRedirect();
        $flash = $crawler->filter('.flash-success')->text();
        // Check the message contains "finalisé" (or "finalized" in English)
        self::assertStringContainsString('finalisé', $flash);

        // Assert - Status should be COMPLETED
        $updatedInventory = $repository->getByUuid(ResourceUuid::fromString($inventoryUuid));
        self::assertTrue($updatedInventory->status()->equals(InventoryStatus::COMPLETED));
    }

    public function testCompleteInventoryFailsOnNonReviewStatus(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

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

        // Act - Try to complete a DRAFT inventory
        $this->client->request(Request::METHOD_POST, \sprintf(self::COMPLETE_URI, $inventoryUuid));

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/inventories/');

        $crawler = $this->client->followRedirect();
        $flash = $crawler->filter('.flash-error')->text();
        self::assertSame($translator->trans('inventory.complete.errors.invalid_status'), $flash);
    }

    public function testCompleteInventoryFailsOnUnreviewedDiscrepancies(): void
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

        // Start the inventory
        $this->client->request(Request::METHOD_POST, \sprintf(self::START_URI, $inventoryUuid));
        $this->client->followRedirect();

        // Record stock with discrepancy
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
                // consumer_unit is the required field (base unit)
                $formData["real_stock_{$articleReal->slug()}_consumer_unit"] = '5';
            }
        }

        $this->client->request(
            Request::METHOD_POST,
            \sprintf(self::RECORD_STOCK_URI, $inventoryUuid, $zoneStorageUuid),
            $formData
        );
        $this->client->followRedirect();

        // Finish counting to go to REVIEW status (but DON'T review discrepancies)
        $this->client->request(Request::METHOD_POST, \sprintf(self::FINISH_COUNTING_URI, $inventoryUuid));
        $this->client->followRedirect();

        // Act - Try to complete without reviewing discrepancies
        $this->client->request(Request::METHOD_POST, \sprintf(self::COMPLETE_URI, $inventoryUuid));

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/inventories/');

        $crawler = $this->client->followRedirect();
        $flash = $crawler->filter('.flash-error')->text();
        self::assertStringContainsString('écart', $flash);
    }

    public function testCompleteInventoryFailsOnInventoryNotFound(): void
    {
        // Arrange
        InventoryStory::load();

        $nonExistentUuid = '00000000-0000-0000-0000-000000000000';

        // Act
        $this->client->request(Request::METHOD_POST, \sprintf(self::COMPLETE_URI, $nonExistentUuid));

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/inventories/');

        $crawler = $this->client->followRedirect();
        $flash = $crawler->filter('.flash-error');
        self::assertGreaterThan(0, $flash->count(), 'Error flash message should be displayed');
    }

    public function testRouteNameConstantExists(): void
    {
        self::assertTrue(\defined(CompleteInventoryController::class . '::ROUTE_NAME'));
        self::assertSame('inventory_complete', CompleteInventoryController::ROUTE_NAME);
    }

    public function testAccessDeniedForRoleUser(): void
    {
        $this->logoutUser();
        $this->authenticateAsRoleUser();
        $this->client->catchExceptions(false);
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Access denied. Required role: ROLE_INVENTORY_MANAGER');
        $this->client->request(Request::METHOD_POST, $this->getProtectedUri());
    }

    protected function getProtectedUri(): string
    {
        // UUID factice, access_control vérifie l'auth avant le routage complet
        return \sprintf(self::COMPLETE_URI, '00000000-0000-0000-0000-000000000000');
    }

    protected function getProtectedHttpMethod(): string
    {
        return Request::METHOD_POST;
    }
}
