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
use Inventory\Adapters\Controller\Symfony\Controller\CreateInventory\CreateInventoryController;
use Inventory\Adapters\Gateway\ORM\Entity\InventoryStatus;
use Inventory\Entities\Exception\EqualOrFutureDateExpected;
use Inventory\Entities\Exception\InventoryAlreadyActiveForZone;
use Inventory\Tests\Factory\InventoryFactory;
use Inventory\Tests\Story\InventoryStory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * @group functionalTest
 *
 * @covers \Inventory\Adapters\Controller\Symfony\Controller\CreateInventory\CreateInventoryController
 */
final class CreateInventoryControllerTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    public const string CREATE_INVENTORY_URI = '/inventories/create';
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

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
        self::assertSelectorExists('select[name="createInventory[date][day]"]');
        self::assertSelectorExists('select[name="createInventory[date][month]"]');
        self::assertSelectorExists('select[name="createInventory[date][year]"]');
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
            'createInventory[date][day]' => (int) $futureDate->format('d'),
            'createInventory[date][month]' => (int) $futureDate->format('m'),
            'createInventory[date][year]' => (int) $futureDate->format('Y'),
            'createInventory[zoneStorages]' => [$zoneStorages[0]->uuid()],
        ]);
        $this->client->submit($form);

        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/inventories');

        $inventory = $this->client->followRedirect();
        $flash = $inventory->filter('body > div.container > div')->children('div.flash.flash-success')->text();

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
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');
        InventoryStory::load();
        $zoneStorages = ZoneStorageFactory::all();
        $pastDate = new \DateTimeImmutable('-1 day');

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_INVENTORY_URI);
        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createInventory[date][day]' => (int) $pastDate->format('d'),
            'createInventory[date][month]' => (int) $pastDate->format('m'),
            'createInventory[date][year]' => (int) $pastDate->format('Y'),
            'createInventory[zoneStorages]' => [$zoneStorages[0]->uuid()],
        ]);
        $this->client->submit($form);

        // Assert - Pas de redirection (erreur validation)
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $inventory = $this->client->followRedirect();
        $flash = $inventory->filter('body > div.container > div')->children('div.flash.flash-error')->text();
        self::assertSame(EqualOrFutureDateExpected::MESSAGE, $flash);

        // Assert - Pas de création en base
        self::assertCount(0, InventoryFactory::all());
    }

    public function testCreateInventoryWhenActiveInventoryExistsForZone(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');
        InventoryStory::load();
        $zoneStorages = ZoneStorageFactory::all();
        InventoryFactory::createOne([
            'date' => new \DateTimeImmutable('+1 day'),
            'zoneStorages' => [$zoneStorages[0]->_real()],
            'status' => InventoryStatus::DRAFT->value,
        ]);

        $futureDate = new \DateTimeImmutable('+1 day');

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_INVENTORY_URI);
        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createInventory[date][day]' => (int) $futureDate->format('d'),
            'createInventory[date][month]' => (int) $futureDate->format('m'),
            'createInventory[date][year]' => (int) $futureDate->format('Y'),
            'createInventory[zoneStorages]' => [$zoneStorages[0]->uuid()],
        ]);
        $this->client->submit($form);

        // Assert - Pas de redirection (erreur validation)
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $inventory = $this->client->followRedirect();
        $flash = $inventory->filter('body > div.container > div')->children('div.flash.flash-error')->text();
        self::assertSame(InventoryAlreadyActiveForZone::MESSAGE, $flash);

        // Assert - Pas de nouveau inventaire créé
        self::assertCount(1, InventoryFactory::all());
    }

    public function testCreateInventoryWithMissingZone(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');
        InventoryStory::load();
        $futureDate = new \DateTimeImmutable('+1 day');

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_INVENTORY_URI);
        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createInventory[date][day]' => (int) $futureDate->format('d'),
            'createInventory[date][month]' => (int) $futureDate->format('m'),
            'createInventory[date][year]' => (int) $futureDate->format('Y'),
            'createInventory[zoneStorages]' => [],
        ]);
        $this->client->submit($form);

        // Assert - Pas de redirection (erreur validation)
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $this->client->followRedirect();

        // Assert - Pas de création en base
        self::assertCount(0, InventoryFactory::all());
    }

    public function testCreateInventoryRouteNameConstantExists(): void
    {
        // Assert
        self::assertTrue(\defined(CreateInventoryController::class . '::ROUTE_NAME'));
        self::assertSame('inventory_create', CreateInventoryController::ROUTE_NAME);
    }
}
