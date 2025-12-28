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
use Inventory\Adapters\Gateway\ORM\Entity\InventoryStatus;
use Inventory\Tests\Factory\InventoryFactory;
use Inventory\Tests\Story\InventoryStory;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\Clock\FrozenClock;
use Shared\Tests\BasePantherTestCase;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group e2eTest
 */
final class RecordRealStockByZoneForAnInventoryTest extends BasePantherTestCase
{
    use Factories;

    protected function setUp(): void
    {
        parent::setUp();
        ClockFactory::initialize(new FrozenClock(new \DateTimeImmutable('today')));
    }

    public function testUserCanNavigateToRecordStockFormAndSeeArticles(): void
    {
        // Arrange
        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

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

        // Act
        $client->request('GET', '/');
        self::assertSelectorTextContains('h1', $translator->trans('home.welcome'));

        $client->clickLink($translator->trans('inventory.titlePage'));

        $client->waitForElementToContain('h1', $translator->trans('inventory.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('inventory.titlePage'));

        $recordStockButtons = $client->getCrawler()->filterXPath(
            \sprintf(
                '//turbo-frame[@id="inventory_%s"]//a[contains(text(), "%s")]',
                $inventory->_real()->uuid(),
                $translator->trans('inventory.zone.record.button')
            )
        );
        self::assertCount(0, $recordStockButtons, '"Saisir stock" button should NOT be visible for DRAFT inventory');

        $startButton = $client->getCrawler()->filter(
            \sprintf('turbo-frame#inventory_%s button[type="submit"]', $inventory->_real()->uuid())
        );
        self::assertCount(1, $startButton, 'Start button should be visible for DRAFT inventory');

        $startButton->first()->click();

        $client->waitForVisibility('.flash-success');
        $flash = $client->getCrawler()->filter('.flash-success')->text();
        self::assertStringContainsString($translator->trans('inventory.start.success'), $flash);

        $client->waitForElementToContain('h1', $translator->trans('inventory.titlePage'));

        $recordStockButtons = $client->getCrawler()->filterXPath(
            \sprintf(
                '//turbo-frame[@id="inventory_%s"]//a[contains(text(), "%s")]',
                $inventory->_real()->uuid(),
                $translator->trans('inventory.zone.record.button')
            )
        );
        self::assertCount(1, $recordStockButtons, '"Saisir stock" button should be visible for IN_PROGRESS inventory');

        $recordStockButtons->first()->click();

        $client->waitForElementToContain('h1', $translator->trans('inventory.zone.record.titlePage'));
        self::assertSelectorTextContains('h1', 'Réserve positive');

        $articleInputs = $client->getCrawler()->filter('input[type="number"]');
        self::assertGreaterThan(0, $articleInputs->count(), 'Form should contain article stock inputs');
    }

    public function testUserCanRecordRealStockAndSeeSuccessMessage(): void
    {
        // Arrange
        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

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

        $inventoryUuid = $inventory->_real()->uuid();

        $client->request('GET', '/inventories');
        $client->waitForElementToContain('h1', $translator->trans('inventory.titlePage'));

        $startButton = $client->getCrawler()->filter(
            \sprintf('turbo-frame#inventory_%s button[type="submit"]', $inventoryUuid)
        );
        $startButton->first()->click();

        $client->waitForVisibility('.flash-success');

        $client->waitForElementToContain('h1', $translator->trans('inventory.titlePage'));

        $recordStockButtons = $client->getCrawler()->filterXPath(
            \sprintf(
                '//turbo-frame[@id="inventory_%s"]//a[contains(text(), "%s")]',
                $inventoryUuid,
                $translator->trans('inventory.zone.record.button')
            )
        );
        $recordStockButtons->first()->click();

        $client->waitForElementToContain('h1', $translator->trans('inventory.zone.record.titlePage'));

        $articleInputs = $client->getCrawler()->filter('input[type="number"][required]');
        self::assertGreaterThan(0, $articleInputs->count());

        // Build form data for all required inputs (all fields are now mandatory)
        $formData = [];
        $articleInputs->each(static function ($node, $i) use (&$formData): void {
            $inputName = $node->attr('name');
            $formData[$inputName] = (string) (10 + $i); // Different value for each input
        });

        $client->submitForm($translator->trans('inventory.zone.record.submit'), $formData);

        $client->waitForVisibility('.flash-success');
        $flash = $client->getCrawler()->filter('.flash-success')->text();
        self::assertStringContainsString($translator->trans('inventory.zone.record.success'), $flash);

        $client->waitForElementToContain('h1', $translator->trans('inventory.titlePage'));
    }

    public function testRecordStockButtonOnlyVisibleForInProgressInventory(): void
    {
        // Arrange
        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        InventoryStory::load();
        $this->flushAndClearEntityManager();

        $now = ClockFactory::clock()->now();
        $zonePositive = ZoneStorageFactory::findBy(['label' => 'Réserve positive'])[0]->_real();

        InventoryFactory::createOne([
            'date' => $now->modify('+1 day'),
            'zoneStorages' => [$zonePositive->uuid()],
            'status' => InventoryStatus::IN_PROGRESS->value,
            'createdAt' => $now,
            'updatedAt' => $now,
            'statusUpdatedAt' => $now,
        ]);
        $this->flushAndClearEntityManager();

        $client->request('GET', '/');
        $client->clickLink($translator->trans('inventory.titlePage'));

        $client->waitForElementToContain('h1', $translator->trans('inventory.titlePage'));

        $recordStockButtons = $client->getCrawler()->filterXPath(
            \sprintf('//a[contains(text(), "%s")]', $translator->trans('inventory.zone.record.button'))
        );
        self::assertGreaterThan(
            0,
            $recordStockButtons->count(),
            '"Saisir stock" button should be visible for IN_PROGRESS inventory'
        );

        $startButtons = $client->getCrawler()->filterXPath(
            \sprintf('//button[contains(text(), "%s")]', $translator->trans('inventory.start.button'))
        );
        self::assertCount(0, $startButtons, 'Start button should NOT be visible for IN_PROGRESS inventory');
    }
}
