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

namespace Inventory\Tests\Adapters\Controller\Symfony\Controller\CancelInventory;

use Admin\Tests\Factory\ZoneStorageFactory;
use Inventory\Adapters\Controller\Symfony\Controller\CancelInventory\CancelInventoryController;
use Inventory\Adapters\Gateway\ORM\Entity\InventoryStatus as ORMInventoryStatus;
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
 * @covers \Inventory\Adapters\Controller\Symfony\Controller\CancelInventory\CancelInventoryController
 */
final class CancelInventoryControllerTest extends BaseFunctionalTestCase
{
    use Factories;

    public const string CANCEL_URI = '/inventories/%s/cancel';
    public const string START_URI = '/inventories/%s/start';

    public function testCancelDraftInventorySuccessfully(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        /** @var InventoryRepository $repository */
        $repository = self::getContainer()->get(InventoryRepository::class);

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

        // Act
        $this->client->request(Request::METHOD_POST, \sprintf(self::CANCEL_URI, $inventoryUuid));

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/inventories');

        $crawler = $this->client->followRedirect();
        $flash = $crawler->filter('.flash-success')->text();
        self::assertSame($translator->trans('inventory.cancel.success'), $flash);

        $updatedInventory = $repository->getByUuid(ResourceUuid::fromString($inventoryUuid));
        self::assertTrue($updatedInventory->status()->equals(InventoryStatus::CANCELLED));
    }

    public function testCancelInProgressInventorySuccessfully(): void
    {
        // Arrange
        /** @var InventoryRepository $repository */
        $repository = self::getContainer()->get(InventoryRepository::class);

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

        // Start the inventory first
        $this->client->request(Request::METHOD_POST, \sprintf(self::START_URI, $inventoryUuid));
        $this->client->followRedirect();

        // Act - Cancel in progress inventory
        $this->client->request(Request::METHOD_POST, \sprintf(self::CANCEL_URI, $inventoryUuid));

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/inventories');

        $this->client->followRedirect();

        $updatedInventory = $repository->getByUuid(ResourceUuid::fromString($inventoryUuid));
        self::assertTrue($updatedInventory->status()->equals(InventoryStatus::CANCELLED));
    }

    public function testCancelInventoryFailsOnCompletedStatus(): void
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
            'status' => ORMInventoryStatus::COMPLETED->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => $now,
        ]);

        $inventoryUuid = $inventory->_real()->uuid();

        // Act
        $this->client->request(Request::METHOD_POST, \sprintf(self::CANCEL_URI, $inventoryUuid));

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/inventories');

        $crawler = $this->client->followRedirect();
        $flash = $crawler->filter('.flash-error')->text();
        self::assertSame($translator->trans('inventory.cancel.errors.cannot_cancel_completed'), $flash);
    }

    public function testCancelInventoryFailsOnInventoryNotFound(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        InventoryStory::load();

        $nonExistentUuid = '00000000-0000-0000-0000-000000000000';

        // Act
        $this->client->request(Request::METHOD_POST, \sprintf(self::CANCEL_URI, $nonExistentUuid));

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/inventories');

        $crawler = $this->client->followRedirect();
        $flash = $crawler->filter('.flash-error')->text();
        self::assertSame($translator->trans('inventory.errors.not_found'), $flash);
    }

    public function testRouteNameConstantExists(): void
    {
        self::assertTrue(\defined(CancelInventoryController::class . '::ROUTE_NAME'));
        self::assertSame('inventory_cancel', CancelInventoryController::ROUTE_NAME);
    }
}
