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

use Admin\Tests\Factory\ArticleFactory;
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
final class CompleteInventoryWorkflowTest extends BasePantherTestCase
{
    use Factories;

    protected function setUp(): void
    {
        parent::setUp();
        ClockFactory::initialize(new FrozenClock(new \DateTimeImmutable('today')));
    }

    public function testCancelInventoryFromDraftState(): void
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
        self::assertCount(1, $cancelButton, 'Cancel button should be visible for DRAFT inventory');
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
        $client->wait(1);

        // Act - Now cancel from IN_PROGRESS
        $client->executeScript('window.confirm = () => true;');

        $cancelButton = $client->getCrawler()->filterXPath(
            \sprintf(
                '//turbo-frame[@id="inventory_%s"]//button[contains(@onclick, "confirm")]',
                $inventoryUuid
            )
        );
        self::assertCount(1, $cancelButton, 'Cancel button should be visible for IN_PROGRESS inventory');
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
        $client->wait(1);

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
        $client->wait(2);

        // Finish counting to get to REVIEW state - reload page to get fresh data
        $client->request('GET', '/inventories');
        $client->waitForElementToContain('h1', $translator->trans('inventory.titlePage'));
        $client->wait(1);

        $finishButton = $client->getCrawler()->filterXPath(
            \sprintf(
                '//turbo-frame[@id="inventory_%s"]//button[contains(., "%s")]',
                $inventoryUuid,
                $translator->trans('inventory.finish_counting.button')
            )
        );
        self::assertCount(1, $finishButton, 'Finish counting button should be visible when all items counted');
        $finishButton->first()->click();
        $client->waitForVisibility('.flash-success');
        $client->wait(1);

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
        self::assertCount(1, $cancelButton, 'Cancel button should be visible for REVIEW inventory');
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

    public function testResumeCountingFromReviewState(): void
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

        // Create inventory and progress to REVIEW state
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

        // Start inventory
        $client->request('GET', '/inventories');
        $client->waitForElementToContain('h1', $translator->trans('inventory.titlePage'));

        $startButton = $client->getCrawler()->filter(
            \sprintf('turbo-frame#inventory_%s button[type="submit"]', $inventoryUuid)
        );
        $startButton->first()->click();
        $client->waitForVisibility('.flash-success');
        $client->wait(1);

        // Record stocks with different values to create discrepancies
        $recordStockButton = $client->getCrawler()->filterXPath(
            \sprintf(
                '//turbo-frame[@id="inventory_%s"]//a[contains(text(), "%s")]',
                $inventoryUuid,
                $translator->trans('inventory.zone.record.button')
            )
        );
        $recordStockButton->first()->click();
        $client->waitForElementToContain('h1', $translator->trans('inventory.zone.record.titlePage'));

        $articleInputs = $client->getCrawler()->filter('input[type="number"][required]');
        $formData = [];
        $articleInputs->each(static function ($node, $i) use (&$formData): void {
            $inputName = $node->attr('name');
            $formData[$inputName] = (string) (5 + $i); // Different values to create discrepancies
        });
        $client->submitForm($translator->trans('inventory.zone.record.submit'), $formData);
        $client->waitForVisibility('.flash-success');
        $client->wait(2);

        // Finish counting
        $client->request('GET', '/inventories');
        $client->waitForElementToContain('h1', $translator->trans('inventory.titlePage'));
        $client->wait(1);

        $finishButton = $client->getCrawler()->filterXPath(
            \sprintf(
                '//turbo-frame[@id="inventory_%s"]//button[contains(., "%s")]',
                $inventoryUuid,
                $translator->trans('inventory.finish_counting.button')
            )
        );
        self::assertCount(1, $finishButton, 'Finish counting button should be visible');
        $finishButton->first()->click();
        $client->waitForVisibility('.flash-success');
        $client->wait(1);

        // Verify inventory is in REVIEW status before looking for Review button
        $this->flushAndClearEntityManager();
        $inventoryAfterFinish = InventoryFactory::find(['uuid' => $inventoryUuid])->_real();
        self::assertSame(
            InventoryStatus::REVIEW->value,
            $inventoryAfterFinish->status()->value,
            'Inventory should be in REVIEW status after finish counting'
        );

        // Act - Navigate to review page and resume counting
        $client->request('GET', '/inventories');
        $client->waitForElementToContain('h1', $translator->trans('inventory.titlePage'));
        $client->wait(1);

        $reviewLink = $client->getCrawler()->filterXPath(
            \sprintf(
                '//turbo-frame[@id="inventory_%s"]//a[contains(@class, "btn-warning")]',
                $inventoryUuid
            )
        );
        self::assertCount(1, $reviewLink, 'Review button should be visible when there are unreviewed discrepancies');
        $reviewLink->first()->click();

        $client->waitForElementToContain('h1', $translator->trans('inventory.review.titlePage'));

        // Open the details accordion for resume counting
        $detailsSummary = $client->getCrawler()->filterXPath(
            \sprintf('//summary[contains(text(), "%s")]', $translator->trans('inventory.resume_counting.button'))
        );
        self::assertCount(1, $detailsSummary, 'Resume counting section should be visible');
        $detailsSummary->first()->click();
        $client->wait(1);

        // Click on the zone button to resume counting
        $resumeZoneButton = $client->getCrawler()->filterXPath(
            \sprintf('//details//button[contains(., "%s")]', $zonePositive->label())
        );
        self::assertCount(1, $resumeZoneButton, 'Resume counting button for zone should be visible');
        $resumeZoneButton->first()->click();

        // Assert
        $client->waitForVisibility('.flash-success');
        $flash = $client->getCrawler()->filter('.flash-success')->text();
        self::assertStringContainsString($translator->trans('inventory.resume_counting.success'), $flash);

        // Verify we're on the record stock page
        $client->waitForElementToContain('h1', $translator->trans('inventory.zone.record.titlePage'));

        // Verify database state - should be back to IN_PROGRESS
        $this->flushAndClearEntityManager();
        $resumedInventory = InventoryFactory::find(['uuid' => $inventoryUuid])->_real();
        self::assertSame(InventoryStatus::IN_PROGRESS->value, $resumedInventory->status()->value);
    }

    public function testCompleteInventoryWorkflowHappyPath(): void
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

        // Get initial article quantities for comparison later
        $initialLait = ArticleFactory::findBy(['name' => 'Lait'])[0]->_real();
        $initialCamembert = ArticleFactory::findBy(['name' => 'Camembert'])[0]->_real();
        $initialLaitQuantity = $initialLait->quantity();
        $initialCamembertQuantity = $initialCamembert->quantity();

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

        // === Step 1: Navigate to inventories page ===
        $client->request('GET', '/inventories');
        $client->waitForElementToContain('h1', $translator->trans('inventory.titlePage'));

        // Verify DRAFT status buttons
        $startButton = $client->getCrawler()->filter(
            \sprintf('turbo-frame#inventory_%s button[type="submit"]', $inventoryUuid)
        );
        self::assertGreaterThan(0, $startButton->count(), 'Start button should be visible for DRAFT inventory');

        // === Step 2: Start inventory (DRAFT -> IN_PROGRESS) ===
        $startButton->first()->click();
        $client->waitForVisibility('.flash-success');
        $flash = $client->getCrawler()->filter('.flash-success')->text();
        self::assertStringContainsString($translator->trans('inventory.start.success'), $flash);
        $client->wait(1);

        // === Step 3: Record stocks with different values (create discrepancies) ===
        $recordStockButton = $client->getCrawler()->filterXPath(
            \sprintf(
                '//turbo-frame[@id="inventory_%s"]//a[contains(text(), "%s")]',
                $inventoryUuid,
                $translator->trans('inventory.zone.record.button')
            )
        );
        self::assertCount(1, $recordStockButton, 'Record stock button should be visible for zone');
        $recordStockButton->first()->click();

        $client->waitForElementToContain('h1', $translator->trans('inventory.zone.record.titlePage'));

        // Record different quantities to create discrepancies
        // Lait: initial 15 -> record 12 (discrepancy -3)
        // Camembert: initial 5 -> record 7 (discrepancy +2)
        $articleInputs = $client->getCrawler()->filter('input[type="number"][required]');
        $formData = [];
        $newQuantities = [12, 7]; // Different from initial 15 and 5
        $articleInputs->each(static function ($node, $i) use (&$formData, $newQuantities): void {
            $inputName = $node->attr('name');
            $formData[$inputName] = (string) $newQuantities[$i];
        });

        $client->submitForm($translator->trans('inventory.zone.record.submit'), $formData);
        $client->waitForVisibility('.flash-success');
        $flash = $client->getCrawler()->filter('.flash-success')->text();
        self::assertStringContainsString($translator->trans('inventory.zone.record.success'), $flash);
        $client->wait(2);

        // === Step 4: Finish counting (IN_PROGRESS -> REVIEW) ===
        $client->request('GET', '/inventories');
        $client->waitForElementToContain('h1', $translator->trans('inventory.titlePage'));
        $client->wait(1);

        $finishButton = $client->getCrawler()->filterXPath(
            \sprintf(
                '//turbo-frame[@id="inventory_%s"]//button[contains(., "%s")]',
                $inventoryUuid,
                $translator->trans('inventory.finish_counting.button')
            )
        );
        self::assertCount(1, $finishButton, 'Finish counting button should be visible');
        $finishButton->first()->click();

        $client->waitForVisibility('.flash-success');
        $flash = $client->getCrawler()->filter('.flash-success')->text();
        self::assertStringContainsString($translator->trans('inventory.finish_counting.success'), $flash);
        $client->wait(1);

        // === Step 5: Review discrepancies ===
        // Verify inventory is in REVIEW status
        $this->flushAndClearEntityManager();
        $inventoryAfterFinish = InventoryFactory::find(['uuid' => $inventoryUuid])->_real();
        self::assertSame(
            InventoryStatus::REVIEW->value,
            $inventoryAfterFinish->status()->value,
            'Inventory should be in REVIEW status'
        );

        // Reload page to get fresh UI
        $client->request('GET', '/inventories');
        $client->waitForElementToContain('h1', $translator->trans('inventory.titlePage'));
        $client->wait(1);

        $reviewLink = $client->getCrawler()->filterXPath(
            \sprintf(
                '//turbo-frame[@id="inventory_%s"]//a[contains(@class, "btn-warning")]',
                $inventoryUuid
            )
        );
        self::assertCount(1, $reviewLink, 'Review button should be visible');
        $reviewLink->first()->click();

        $client->waitForElementToContain('h1', $translator->trans('inventory.review.titlePage'));

        // Verify we have discrepancies to review (check if the form exists)
        $form = $client->getCrawler()->filter('form');
        self::assertGreaterThan(0, $form->count(), 'Review form should be present (discrepancies exist)');

        // Verify we have unreviewed items with pending status
        $pendingBadges = $client->getCrawler()->filterXPath(
            \sprintf('//span[contains(@class, "badge-warning") and contains(., "%s")]', $translator->trans('inventory.review.pending'))
        );
        self::assertGreaterThan(0, $pendingBadges->count(), 'Should have pending items to review');

        // === Step 6: Mark all items as reviewed ===
        // Check all checkboxes using JavaScript
        $client->executeScript('document.querySelectorAll(\'.item-checkbox\').forEach(cb => cb.checked = true);');
        $client->wait(1);

        // Find and click the submit button
        $submitButton = $client->getCrawler()->filterXPath(
            \sprintf('//button[contains(., "%s")]', $translator->trans('inventory.review.mark_as_reviewed'))
        );
        self::assertCount(1, $submitButton, 'Submit button should exist');
        $submitButton->first()->click();

        $client->waitForVisibility('.flash-success');
        $flash = $client->getCrawler()->filter('.flash-success')->text();
        self::assertStringContainsString($translator->trans('inventory.review.items_reviewed'), $flash);
        $client->wait(1);

        // === Step 7: Complete inventory (REVIEW -> COMPLETED) ===
        $client->request('GET', '/inventories');
        $client->waitForElementToContain('h1', $translator->trans('inventory.titlePage'));
        $client->wait(1);

        $completeButton = $client->getCrawler()->filterXPath(
            \sprintf(
                '//turbo-frame[@id="inventory_%s"]//button[contains(., "%s")]',
                $inventoryUuid,
                $translator->trans('inventory.complete.submit')
            )
        );
        self::assertCount(1, $completeButton, 'Complete button should be visible when all items reviewed');
        $completeButton->first()->click();

        $client->waitForVisibility('.flash-success');
        $flash = $client->getCrawler()->filter('.flash-success')->text();
        // Check for the static part of the message (the count is dynamic)
        self::assertStringContainsString('Inventaire finalisé avec succès', $flash);
        self::assertStringContainsString('article(s) mis à jour', $flash);

        // === Step 8: Verify final state ===
        $this->flushAndClearEntityManager();

        // Verify inventory is COMPLETED
        $completedInventory = InventoryFactory::find(['uuid' => $inventoryUuid])->_real();
        self::assertSame(InventoryStatus::COMPLETED->value, $completedInventory->status()->value);

        // Verify article stocks have been updated
        $updatedLait = ArticleFactory::findBy(['name' => 'Lait'])[0]->_real();
        $updatedCamembert = ArticleFactory::findBy(['name' => 'Camembert'])[0]->_real();

        self::assertNotEquals(
            $initialLaitQuantity,
            $updatedLait->quantity(),
            'Lait stock should have been updated after inventory completion'
        );
        self::assertNotEquals(
            $initialCamembertQuantity,
            $updatedCamembert->quantity(),
            'Camembert stock should have been updated after inventory completion'
        );
    }
}
