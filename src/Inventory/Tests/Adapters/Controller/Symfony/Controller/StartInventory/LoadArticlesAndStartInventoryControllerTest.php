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

namespace Inventory\Tests\Adapters\Controller\Symfony\Controller\StartInventory;

use Admin\Tests\Factory\ZoneStorageFactory;
use Inventory\Adapters\Controller\Symfony\Controller\StartInventory\LoadArticlesAndStartInventoryController;
use Inventory\Adapters\Gateway\ORM\Entity\InventoryStatus;
use Inventory\Entities\Exception\CannotLoadArticlesOnNonDraftInventory;
use Inventory\Entities\Exception\NoArticlesToLoad;
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
 * @covers \Inventory\Adapters\Controller\Symfony\Controller\StartInventory\LoadArticlesAndStartInventoryController
 */
final class LoadArticlesAndStartInventoryControllerTest extends BaseFunctionalTestCase
{
    use Factories;

    public const string START_INVENTORY_URI = '/inventories/%s/start';

    public function testStartInventorySuccess(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');
        InventoryStory::load();

        $now = ClockFactory::clock()->now();
        $futureDate = $now->modify('+1 day');

        $zonePositive = ZoneStorageFactory::findBy(['label' => 'Réserve positive'])[0];

        $inventory = InventoryFactory::createOne([
            'date' => $futureDate,
            'zoneStorages' => [$zonePositive->_real()->uuid()],
            'status' => InventoryStatus::DRAFT->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => null,
        ]);

        $inventoryUuid = $inventory->_real()->uuid();
        $uri = \sprintf(self::START_INVENTORY_URI, $inventoryUuid);

        // Act
        $this->client->request(Request::METHOD_POST, $uri);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/inventories');

        $crawler = $this->client->followRedirect();
        $flash = $crawler->filter('.flash-success')->text();
        self::assertSame($translator->trans('inventory.start.success'), $flash);

        $updatedInventory = InventoryFactory::find(['uuid' => $inventoryUuid])->_real();
        self::assertSame(InventoryStatus::IN_PROGRESS, $updatedInventory->status());
    }

    public function testStartInventoryFailsOnNonDraft(): void
    {
        // Arrange
        InventoryStory::load();

        $now = ClockFactory::clock()->now();
        $zonePositive = ZoneStorageFactory::findBy(['label' => 'Réserve positive'])[0];

        $inventory = InventoryFactory::createOne([
            'date' => $now->modify('+1 day'),
            'zoneStorages' => [$zonePositive->_real()->uuid()],
            'status' => InventoryStatus::IN_PROGRESS->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => $now,
        ]);

        $uri = \sprintf(self::START_INVENTORY_URI, $inventory->_real()->uuid());

        // Act
        $this->client->request(Request::METHOD_POST, $uri);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/inventories');

        $crawler = $this->client->followRedirect();
        $flash = $crawler->filter('.flash-error')->text();
        self::assertSame(CannotLoadArticlesOnNonDraftInventory::MESSAGE, $flash);
    }

    public function testStartInventoryFailsOnNoArticlesInZone(): void
    {
        // Arrange
        InventoryStory::load();

        $now = ClockFactory::clock()->now();

        $emptyZone = ZoneStorageFactory::findBy(['label' => 'Réserve négative'])[0];

        $inventory = InventoryFactory::createOne([
            'date' => $now->modify('+1 day'),
            'zoneStorages' => [$emptyZone->_real()->uuid()],
            'status' => InventoryStatus::DRAFT->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => null,
        ]);

        $uri = \sprintf(self::START_INVENTORY_URI, $inventory->_real()->uuid());

        // Act
        $this->client->request(Request::METHOD_POST, $uri);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/inventories');

        $crawler = $this->client->followRedirect();
        $flash = $crawler->filter('.flash-error')->text();
        self::assertSame(NoArticlesToLoad::MESSAGE, $flash);
    }

    public function testStartInventoryRouteNameConstantExists(): void
    {
        self::assertTrue(\defined(LoadArticlesAndStartInventoryController::class . '::ROUTE_NAME'));
        self::assertSame('inventory_start', LoadArticlesAndStartInventoryController::ROUTE_NAME);
    }
}
