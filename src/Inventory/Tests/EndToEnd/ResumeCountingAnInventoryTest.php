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
final class ResumeCountingAnInventoryTest extends BasePantherTestCase
{
    use AuthenticatedPantherTestTrait;
    use Factories;
    use InventoryE2ETestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpFrozenClock();
    }

    public function testResumeCountingFromReviewState(): void
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

        // Finish counting
        $client->request('GET', '/inventories');
        $client->waitForElementToContain('h1', $translator->trans('inventory.titlePage'));

        $finishButton = $client->getCrawler()->filterXPath(
            \sprintf(
                '//turbo-frame[@id="inventory_%s"]//button[contains(., "%s")]',
                $inventoryUuid,
                $translator->trans('inventory.finish_counting.button')
            )
        );
        self::assertCount(1, $finishButton, 'Le bouton Terminer le comptage devrait être visible');
        $finishButton->first()->click();
        $client->waitForVisibility('.flash-success');

        // Verify inventory is in REVIEW status before looking for Review button
        $this->flushAndClearEntityManager();
        $inventoryAfterFinish = InventoryFactory::find(['uuid' => $inventoryUuid])->_real();
        self::assertSame(
            InventoryStatus::REVIEW->value,
            $inventoryAfterFinish->status()->value,
            'L\'inventaire devrait être en statut REVIEW après le comptage'
        );

        // Act - Navigate to review page and resume counting
        $client->request('GET', '/inventories');
        $client->waitForElementToContain('h1', $translator->trans('inventory.titlePage'));

        $reviewLink = $client->getCrawler()->filterXPath(
            \sprintf(
                '//turbo-frame[@id="inventory_%s"]//a[contains(@class, "btn-warning")]',
                $inventoryUuid
            )
        );
        self::assertCount(1, $reviewLink, 'Le bouton Réviser devrait être visible quand il y a des écarts non révisés');
        $reviewLink->first()->click();

        $client->waitForElementToContain('h1', $translator->trans('inventory.review.titlePage'));

        // Open the details accordion for resume counting
        $detailsSummary = $client->getCrawler()->filterXPath(
            \sprintf('//summary[contains(text(), "%s")]', $translator->trans('inventory.resume_counting.button'))
        );
        self::assertCount(1, $detailsSummary, 'La section Reprendre le comptage devrait être visible');
        $detailsSummary->first()->click();

        // Wait for accordion content to be visible (details[open] means accordion is expanded)
        $client->waitForVisibility('details[open] button');

        // Click on the zone button to resume counting
        $resumeZoneButton = $client->getCrawler()->filterXPath(
            \sprintf('//details//button[contains(., "%s")]', $zonePositive->label())
        );
        self::assertCount(1, $resumeZoneButton, 'Le bouton Reprendre le comptage pour la zone devrait être visible');
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
}
