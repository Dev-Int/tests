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
use Shared\Tests\BasePantherTestCase;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group e2eTest
 */
final class CompleteAnInventoryTest extends BasePantherTestCase
{
    use Factories;
    use InventoryE2ETestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpFrozenClock();
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

        // === Step 4: Finish counting (IN_PROGRESS -> REVIEW) ===
        $client->request('GET', '/inventories');
        $client->waitForElementToContain('h1', $translator->trans('inventory.titlePage'));

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

        // Find and click the submit button
        $submitButton = $client->getCrawler()->filterXPath(
            \sprintf('//button[contains(., "%s")]', $translator->trans('inventory.review.mark_as_reviewed'))
        );
        self::assertCount(1, $submitButton, 'Submit button should exist');
        $submitButton->first()->click();

        $client->waitForVisibility('.flash-success');
        $flash = $client->getCrawler()->filter('.flash-success')->text();
        self::assertStringContainsString($translator->trans('inventory.review.items_reviewed'), $flash);

        // === Step 7: Complete inventory (REVIEW -> COMPLETED) ===
        $client->request('GET', '/inventories');
        $client->waitForElementToContain('h1', $translator->trans('inventory.titlePage'));

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
