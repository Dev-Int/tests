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

use Admin\Tests\Factory\ZoneStorageFactory;
use App\Shared\Tests\BasePantherTestCase;
use Inventory\Adapters\Controller\Symfony\Controller\GetInventories\GetInventoriesController;
use Inventory\Adapters\Gateway\ORM\Entity\InventoryStatus;
use Inventory\Tests\Factory\InventoryFactory;
use Inventory\Tests\Story\InventoryStory;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\Clock\FrozenClock;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group e2eTest
 */
final class StartAnInventoryTest extends BasePantherTestCase
{
    use Factories;

    protected function setUp(): void
    {
        parent::setUp();
        ClockFactory::initialize(new FrozenClock(new \DateTimeImmutable('2025-12-21')));
    }

    public function testUserCanStartDraftInventoryAndSeeFlashMessage(): void
    {
        // Arrange
        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

        $now = ClockFactory::clock()->now();
        $futureDate = $now->modify('+1 day');

        InventoryStory::load();
        $this->flushAndClearEntityManager();

        $zonePositive = ZoneStorageFactory::findBy(['label' => 'Réserve positive'])[0]->_real();

        $inventory = InventoryFactory::createOne([
            'date' => $futureDate,
            'zoneStorages' => [$zonePositive->uuid()],
            'status' => InventoryStatus::DRAFT->value,
            'amount' => 0,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => null,
        ]);
        $this->flushAndClearEntityManager();
        self::assertCount(1, InventoryFactory::all());

        $inventoryUuid = $inventory->_real()->uuid();

        // Act && Assert
        $client->request('GET', '/');
        self::assertSelectorTextContains('h1', $translator->trans('home.welcome'));

        $client->clickLink($translator->trans('inventory.titlePage'));

        $client->waitForElementToContain('h1', $translator->trans('inventory.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('inventory.titlePage'));

        // Assert - Start button is visible for DRAFT inventory
        $startButton = $client->getCrawler()->filter('button[type="submit"]');
        self::assertGreaterThan(0, $startButton->count(), 'Start button should be visible');
        self::assertStringContainsString(
            $translator->trans('inventory.start.button'),
            $startButton->first()->text()
        );

        // Act - Click the start button (submit the form)
        $startButton->first()->click();

        // Wait for page to reload and flash message to appear
        $client->waitForVisibility('.flash-success');

        // Assert - Flash message is displayed
        $flash = $client->getCrawler()->filter('.flash-success')->text();
        self::assertStringContainsString($translator->trans('inventory.start.success'), $flash);

        // Assert - We are still on the inventories page
        $getInventoriesUrl = $router->generate(GetInventoriesController::ROUTE_NAME);
        self::assertStringContainsString($getInventoriesUrl, $client->getCurrentURL());

        // Assert - Start button is no longer visible for this inventory (status changed)
        $client->waitForElementToContain('h1', $translator->trans('inventory.titlePage'));
        $remainingStartButtons = $client->getCrawler()->filter(
            \sprintf('turbo-frame#inventory_%s button[type="submit"]', $inventoryUuid)
        );
        self::assertCount(0, $remainingStartButtons, 'Start button should disappear after starting');

        // Assert - Database status is updated to IN_PROGRESS
        // Clear EntityManager cache to get fresh data from DB (updated by web server)
        $this->flushAndClearEntityManager();
        $updatedInventory = InventoryFactory::find(['uuid' => $inventoryUuid])->_real();
        self::assertSame(InventoryStatus::IN_PROGRESS, $updatedInventory->status());
    }

    public function testStartButtonOnlyVisibleForDraftInventories(): void
    {
        // Arrange
        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        InventoryStory::load();
        $this->flushAndClearEntityManager();

        $now = ClockFactory::clock()->now();
        $zonePositive = ZoneStorageFactory::findBy(['label' => 'Réserve positive'])[0]->_real();

        // Create an IN_PROGRESS inventory (should NOT have start button)
        InventoryFactory::createOne([
            'date' => $now->modify('+1 day'),
            'zoneStorages' => [$zonePositive->uuid()],
            'status' => InventoryStatus::IN_PROGRESS->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => $now,
        ]);
        $this->flushAndClearEntityManager();

        // Act - Navigate from home to inventories page
        $client->request('GET', '/');
        self::assertSelectorTextContains('h1', $translator->trans('home.welcome'));

        $client->clickLink($translator->trans('inventory.titlePage'));

        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('inventory.titlePage'));

        // Assert - No start button should be visible (inventory is not DRAFT)
        $startButtons = $client->getCrawler()->filter('button[type="submit"]');
        self::assertCount(0, $startButtons, 'Start button should NOT be visible for IN_PROGRESS inventory');
    }
}
