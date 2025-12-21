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

namespace Inventory\Tests\Adapters\Controller\Symfony\Controller\CreateInventory;

use Admin\Tests\Factory\ZoneStorageFactory;
use App\Shared\Tests\BaseFunctionalTestCase;
use Inventory\Adapters\Controller\Symfony\Controller\CreateInventory\CreateInventoryController;
use Inventory\Adapters\Gateway\ORM\Entity\InventoryStatus;
use Inventory\Entities\Exception\EqualOrFutureDateExpected;
use Inventory\Entities\Exception\InventoryAlreadyActiveForZone;
use Inventory\Tests\Factory\InventoryFactory;
use Inventory\Tests\Story\InventoryStory;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\Clock\FrozenClock;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 *
 * @covers \Inventory\Adapters\Controller\Symfony\Controller\CreateInventory\CreateInventoryController
 */
final class CreateInventoryControllerTest extends BaseFunctionalTestCase
{
    use Factories;

    public const string CREATE_INVENTORY_URI = '/inventories/create';

    public function testCreateInventoryFormIsDisplayed(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');
        InventoryStory::load();
        $zoneStorages = ZoneStorageFactory::all();

        // Act
        $this->client->request(Request::METHOD_GET, self::CREATE_INVENTORY_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('inventory.create.titlePage'));

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form[name="createInventory"]');
        self::assertSelectorExists('input[name="createInventory[date]"]');
        self::assertSelectorExists('select[name="createInventory[zoneStorages][]"]');
        self::assertSelectorTextContains(
            'select[name="createInventory[zoneStorages][]"] option',
            $zoneStorages[0]->label()
        );
    }

    public function testCreateInventoryWithValidData(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');
        InventoryStory::load();
        $zoneStorages = ZoneStorageFactory::all();
        $futureDate = new \DateTimeImmutable('+1 day');

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_INVENTORY_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('inventory.create.titlePage'));

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createInventory[date]' => $futureDate->format('Y-m-d'),
            'createInventory[zoneStorages]' => [$zoneStorages[0]->uuid()],
        ]);
        $this->client->submit($form);

        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/inventories');

        $inventory = $this->client->followRedirect();
        $flash = $inventory->filter('.flash-success')->text();

        self::assertEquals($translator->trans('inventory.create.success'), $flash);

        self::assertCount(1, InventoryFactory::all());
        $inventory = InventoryFactory::first(sortBy: 'date')->_real();
        self::assertSame($futureDate->format('Y-m-d'), $inventory->date()->format('Y-m-d'));
        self::assertSame(
            $zoneStorages[0]->_real()->uuid(),
            $inventory->zoneStorages()[0]
        );
        self::assertSame(InventoryStatus::DRAFT, $inventory->status());
    }

    public function testCreateInventoryFailWithPastDate(): void
    {
        // Arrange
        ClockFactory::initialize(new FrozenClock(new \DateTimeImmutable('2025-12-01')));
        $now = ClockFactory::clock()->now();

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');
        InventoryStory::load();
        $zoneStorages = ZoneStorageFactory::all();
        $pastDate = $now->modify('-1 day');

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_INVENTORY_URI);
        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createInventory[date]' => $pastDate->format('Y-m-d'),
            'createInventory[zoneStorages]' => [$zoneStorages[0]->uuid()],
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects();

        $inventory = $this->client->followRedirect();

        $flashElements = $inventory->filter('.flash-error');
        $flash = $flashElements->text();
        self::assertSame(EqualOrFutureDateExpected::MESSAGE, $flash);

        // Assert
        self::assertCount(0, InventoryFactory::all());
    }

    public function testCreateInventoryWhenActiveInventoryExistsForZone(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');
        InventoryStory::load();
        $zoneStorages = ZoneStorageFactory::all();
        $firstZone = $zoneStorages[0]->_real();
        $zoneUuid = $firstZone->uuid();

        $futureDate = new \DateTimeImmutable('+1 day');
        $now = ClockFactory::clock()->now();
        InventoryFactory::createOne([
            'date' => $futureDate,
            'zoneStorages' => [$zoneUuid],
            'status' => InventoryStatus::DRAFT->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'settledAt' => $now,
        ]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_INVENTORY_URI);
        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createInventory[date]' => $futureDate->format('Y-m-d'),
            'createInventory[zoneStorages]' => [$zoneUuid],
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $inventory = $this->client->followRedirect();
        $flash = $inventory->filter('.flash-error')->text();
        self::assertSame(InventoryAlreadyActiveForZone::MESSAGE, $flash);

        // Assert
        self::assertCount(1, InventoryFactory::all());
    }

    public function testCreateInventoryWithMissingZone(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');
        InventoryStory::load();
        $allZones = ZoneStorageFactory::all();
        $futureDate = new \DateTimeImmutable('+1 day');

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_INVENTORY_URI);
        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createInventory[date]' => $futureDate->format('Y-m-d'),
            'createInventory[zoneStorages]' => [],
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/inventories');

        $inventory = $this->client->followRedirect();
        $flash = $inventory->filter('.flash-success')->text();

        self::assertEquals($translator->trans('inventory.create.success'), $flash);

        self::assertCount(1, InventoryFactory::all());
        $createdInventory = InventoryFactory::first(sortBy: 'date')->_real();
        self::assertSame($futureDate->format('Y-m-d'), $createdInventory->date()->format('Y-m-d'));

        self::assertCount(\count($allZones), $createdInventory->zoneStorages());

        $createdZoneUuids = $createdInventory->zoneStorages();
        sort($createdZoneUuids);
        $expectedZoneUuids = array_map(static fn ($zone) => $zone->_real()->uuid(), $allZones);
        sort($expectedZoneUuids);

        self::assertSame($expectedZoneUuids, $createdZoneUuids);
    }

    public function testCreateInventoryRouteNameConstantExists(): void
    {
        // Assert
        self::assertTrue(\defined(CreateInventoryController::class . '::ROUTE_NAME'));
        self::assertSame('inventory_create', CreateInventoryController::ROUTE_NAME);
    }
}
