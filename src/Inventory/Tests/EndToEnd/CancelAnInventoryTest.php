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
use Shared\Tests\AuthenticatedPantherTestTrait;
use Shared\Tests\BasePantherTestCase;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group e2eTest
 */
final class CancelAnInventoryTest extends BasePantherTestCase
{
    use AuthenticatedPantherTestTrait;
    use Factories;
    use InventoryE2ETestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpFrozenClock();
    }

    public function testCancelInventoryFromDraftState(): void
    {
        // Arrange
        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $this->loginViaForm($client, $translator);

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

        // Act
        $client->request('GET', '/inventories');
        $client->waitForElementToContain('h1', $translator->trans('inventory.titlePage'));

        // Bypass JS confirm dialog
        $client->executeScript('window.confirm = () => true;');

        // Find and click the cancel button in the inventory turbo-frame
        $cancelButton = $client->getCrawler()->filterXPath(
            \sprintf(
                '//turbo-frame[@id="inventory_%s"]//button[contains(@onclick, "confirm")]',
                $inventoryUuid
            )
        );
        self::assertCount(1, $cancelButton, 'Le bouton Annuler devrait être visible pour un inventaire DRAFT');
        $cancelButton->first()->click();

        // Assert
        $client->waitForVisibility('.flash-success');
        $flash = $client->getCrawler()->filter('.flash-success')->text();
        self::assertStringContainsString($translator->trans('inventory.cancel.success'), $flash);

        // Verify database state
        $this->flushAndClearEntityManager();
        $cancelledInventory = InventoryFactory::find(['uuid' => $inventoryUuid])->_real();
        self::assertSame(InventoryStatus::CANCELLED->value, $cancelledInventory->status()->value);
    }

    public function testCancelInventoryFromInProgressState(): void
    {
        // Arrange
        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $this->loginViaForm($client, $translator);

        $now = ClockFactory::clock()->now();
        $futureDate = $now->modify('+1 day');

        InventoryStory::load();
        $this->flushAndClearEntityManager();

        $zonePositive = ZoneStorageFactory::findBy(['label' => 'Réserve positive'])[0]->_real();

        // Create inventory in DRAFT state first, then start it via UI to have items loaded
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

        // Start the inventory first to get to IN_PROGRESS state
        $client->request('GET', '/inventories');
        $client->waitForElementToContain('h1', $translator->trans('inventory.titlePage'));

        $startButton = $client->getCrawler()->filter(
            \sprintf('turbo-frame#inventory_%s button[type="submit"]', $inventoryUuid)
        );
        $startButton->first()->click();
        $client->waitForVisibility('.flash-success');

        // Act - Now cancel from IN_PROGRESS
        $client->executeScript('window.confirm = () => true;');

        $cancelButton = $client->getCrawler()->filterXPath(
            \sprintf(
                '//turbo-frame[@id="inventory_%s"]//button[contains(@onclick, "confirm")]',
                $inventoryUuid
            )
        );
        self::assertCount(1, $cancelButton, 'Le bouton Annuler devrait être visible pour un inventaire IN_PROGRESS');
        $cancelButton->first()->click();

        // Assert
        $client->waitForVisibility('.flash-success');
        $flash = $client->getCrawler()->filter('.flash-success')->text();
        self::assertStringContainsString($translator->trans('inventory.cancel.success'), $flash);

        // Verify database state
        $this->flushAndClearEntityManager();
        $cancelledInventory = InventoryFactory::find(['uuid' => $inventoryUuid])->_real();
        self::assertSame(InventoryStatus::CANCELLED->value, $cancelledInventory->status()->value);
    }

    public function testCancelInventoryFromReviewState(): void
    {
        // Arrange
        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $this->loginViaForm($client, $translator);

        $now = ClockFactory::clock()->now();
        $futureDate = $now->modify('+1 day');

        InventoryStory::load();
        $this->flushAndClearEntityManager();

        $zonePositive = ZoneStorageFactory::findBy(['label' => 'Réserve positive'])[0]->_real();

        // Create inventory in DRAFT, start it, record stock, then finish counting to get to REVIEW
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

        // Start the inventory
        $client->request('GET', '/inventories');
        $client->waitForElementToContain('h1', $translator->trans('inventory.titlePage'));

        $startButton = $client->getCrawler()->filter(
            \sprintf('turbo-frame#inventory_%s button[type="submit"]', $inventoryUuid)
        );
        $startButton->first()->click();
        $client->waitForVisibility('.flash-success');

        // Record stocks for the zone to be able to finish counting
        $recordStockButton = $client->getCrawler()->filterXPath(
            \sprintf(
                '//turbo-frame[@id="inventory_%s"]//a[contains(text(), "%s")]',
                $inventoryUuid,
                $translator->trans('inventory.zone.record.button')
            )
        );
        $recordStockButton->first()->click();
        $client->waitForElementToContain('h1', $translator->trans('inventory.zone.record.titlePage'));

        // Fill all required stock inputs
        $articleInputs = $client->getCrawler()->filter('input[type="number"][required]');
        $formData = [];
        $articleInputs->each(static function ($node, $i) use (&$formData): void {
            $inputName = $node->attr('name');
            $formData[$inputName] = (string) (10 + $i);
        });
        $client->submitForm($translator->trans('inventory.zone.record.submit'), $formData);
        $client->waitForVisibility('.flash-success');

        // Finish counting to get to REVIEW state - reload page to get fresh data
        $client->request('GET', '/inventories');
        $client->waitForElementToContain('h1', $translator->trans('inventory.titlePage'));

        $finishButton = $client->getCrawler()->filterXPath(
            \sprintf(
                '//turbo-frame[@id="inventory_%s"]//button[contains(., "%s")]',
                $inventoryUuid,
                $translator->trans('inventory.finish_counting.button')
            )
        );
        self::assertCount(1, $finishButton, 'Le bouton Terminer le comptage devrait être visible quand tous les items sont comptés');
        $finishButton->first()->click();
        $client->waitForVisibility('.flash-success');

        // Act - Now cancel from REVIEW
        $client->request('GET', '/inventories');
        $client->waitForElementToContain('h1', $translator->trans('inventory.titlePage'));

        $client->executeScript('window.confirm = () => true;');

        $cancelButton = $client->getCrawler()->filterXPath(
            \sprintf(
                '//turbo-frame[@id="inventory_%s"]//button[contains(@onclick, "confirm")]',
                $inventoryUuid
            )
        );
        self::assertCount(1, $cancelButton, 'Le bouton Annuler devrait être visible pour un inventaire REVIEW');
        $cancelButton->first()->click();

        // Assert
        $client->waitForVisibility('.flash-success');
        $flash = $client->getCrawler()->filter('.flash-success')->text();
        self::assertStringContainsString($translator->trans('inventory.cancel.success'), $flash);

        // Verify database state
        $this->flushAndClearEntityManager();
        $cancelledInventory = InventoryFactory::find(['uuid' => $inventoryUuid])->_real();
        self::assertSame(InventoryStatus::CANCELLED->value, $cancelledInventory->status()->value);
    }
}
