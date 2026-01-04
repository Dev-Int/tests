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

namespace Inventory\Tests\EndToEnd;

use Inventory\Adapters\Controller\Symfony\Controller\GetInventories\GetInventoriesController;
use Inventory\Tests\Factory\InventoryFactory;
use Inventory\Tests\Story\InventoryStory;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\Clock\FrozenClock;
use Shared\Tests\BasePantherTestCase;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group e2eTest
 */
final class CreateAnInventoryTest extends BasePantherTestCase
{
    use Factories;

    protected function setUp(): void
    {
        parent::setUp();
        ClockFactory::initialize(new FrozenClock(new \DateTimeImmutable('today')));
    }

    public function testUserCanCreateInventoryViaForm(): void
    {
        // Arrange
        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

        InventoryStory::load();
        $this->flushAndClearEntityManager();

        $futureDate = ClockFactory::clock()->now()->modify('+5 days');
        $expectedDate = $futureDate->format('Y-m-d');

        // Act && Assert
        $client->request('GET', '/');
        self::assertSelectorTextContains('h1', $translator->trans('home.welcome'));

        $client->clickLink($translator->trans('inventory.titlePage'));

        $client->waitForElementToContain('h1', $translator->trans('inventory.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('inventory.titlePage'));

        $client->clickLink($translator->trans('inventory.create.titleShort'));

        $client->waitForVisibility('form[name="createInventory"]');
        $client->waitForElementToContain(
            'turbo-frame#inventory_create h3',
            $translator->trans('inventory.create.titlePage')
        );
        self::assertSelectorTextContains(
            'turbo-frame#inventory_create h3',
            $translator->trans('inventory.create.titlePage')
        );

        $client->waitForVisibility('button[type="submit"]');

        self::assertSelectorExists('input[name="createInventory[date]"][data-controller="datepicker"]');
        self::assertSelectorExists('select[name="createInventory[zoneStorages][]"]');

        $allZones = InventoryStory::getAllZones();
        $selectOptions = $client->getCrawler()->filter('select[name="createInventory[zoneStorages][]"] option');
        self::assertCount(\count($allZones), $selectOptions);

        // Set date value via JS (flatpickr makes the input hidden)
        $client->executeScript(
            "document.querySelector('input[name=\"createInventory[date]\"]').value = '{$expectedDate}';"
        );
        $client->submitForm($translator->trans('add'));

        $client->waitForElementToContain('h1', $translator->trans('inventory.titlePage'));

        $getInventoriesUrl = $router->generate(GetInventoriesController::ROUTE_NAME);
        self::assertStringContainsString($getInventoriesUrl, $client->getCurrentURL());

        self::assertSelectorNotExists(
            'turbo-frame#inventory_create h3',
            $translator->trans('inventory.create.titlePage')
        );
        self::assertSelectorTextContains('h1', $translator->trans('inventory.titlePage'));

        self::assertSelectorTextContains('ul.table', $expectedDate);

        self::assertCount(1, InventoryFactory::all());
        $inventory = InventoryFactory::first(sortBy: 'date')->_real();
        self::assertSame($expectedDate, $inventory->date()->format('Y-m-d'));
        self::assertCount(\count($allZones), $inventory->zoneStorages());
    }

    public function testUserSeesValidationErrorForPastDate(): void
    {
        // Arrange
        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        InventoryStory::load();

        $pastDate = ClockFactory::clock()->now()->modify('-1 day');
        $expectedDate = $pastDate->format('Y-m-d');

        // Act && Assert
        $client->request('GET', '/');
        self::assertSelectorTextContains('h1', $translator->trans('home.welcome'));

        $client->clickLink($translator->trans('inventory.titlePage'));

        $client->waitForElementToContain('h1', $translator->trans('inventory.titlePage'));

        $client->clickLink($translator->trans('inventory.create.titleShort'));

        $client->waitForVisibility('form[name="createInventory"]');
        $client->waitForElementToContain(
            'turbo-frame#inventory_create h3',
            $translator->trans('inventory.create.titlePage')
        );

        $client->waitForVisibility('button[type="submit"]');

        // Set date value via JS (flatpickr makes the input hidden)
        $client->executeScript(
            "document.querySelector('input[name=\"createInventory[date]\"]').value = '{$expectedDate}';"
        );
        $client->submitForm($translator->trans('add'));

        $client->waitForElementToContain('h1', $translator->trans('inventory.titlePage'));

        self::assertCount(0, InventoryFactory::all());
    }

    public function testUserCanSelectMultipleZones(): void
    {
        // Arrange
        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        InventoryStory::load();

        $futureDate = ClockFactory::clock()->now()->modify('+5 days');
        $expectedDate = $futureDate->format('Y-m-d');

        // Act && Assert
        $client->request('GET', '/');
        self::assertSelectorTextContains('h1', $translator->trans('home.welcome'));

        $client->clickLink($translator->trans('inventory.titlePage'));

        $client->waitForElementToContain('h1', $translator->trans('inventory.titlePage'));

        $client->clickLink($translator->trans('inventory.create.titleShort'));

        $client->waitForVisibility('form[name="createInventory"]');
        $client->waitForElementToContain(
            'turbo-frame#inventory_create h3',
            $translator->trans('inventory.create.titlePage')
        );

        $client->waitForVisibility('select[name="createInventory[zoneStorages][]"]');
        self::assertSelectorExists('select[name="createInventory[zoneStorages][]"][multiple]');

        $options = $client->getCrawler()->filter('select[name="createInventory[zoneStorages][]"] option');
        self::assertGreaterThanOrEqual(2, $options->count());

        $client->waitForVisibility('button[type="submit"]');

        $firstOption = $options->eq(0);
        $secondOption = $options->eq(1);

        // Set date value via JS (flatpickr makes the input hidden)
        $client->executeScript(
            "document.querySelector('input[name=\"createInventory[date]\"]').value = '{$expectedDate}';"
        );
        $client->submitForm($translator->trans('add'), [
            'createInventory[zoneStorages]' => [
                $firstOption->attr('value'),
                $secondOption->attr('value'),
            ],
        ]);

        $client->waitForElementToContain('h1', $translator->trans('inventory.titlePage'));

        self::assertCount(1, InventoryFactory::all());
        $inventory = InventoryFactory::first(sortBy: 'date')->_real();
        self::assertCount(2, $inventory->zoneStorages());
    }
}
