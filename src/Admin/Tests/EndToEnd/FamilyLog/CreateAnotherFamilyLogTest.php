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

namespace Admin\Tests\EndToEnd\FamilyLog;

use Admin\Adapters\Controller\Symfony\Controller\FamilyLog\CreateFamilyLog\CreateFamilyLogController;
use Admin\Adapters\Controller\Symfony\Controller\FamilyLog\GetFamilyLogs\GetFamilyLogsController;
use Shared\Tests\BasePantherTestCase;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group e2eTest
 */
final class CreateAnotherFamilyLogTest extends BasePantherTestCase
{
    public function testCreateAnotherFamilyLogSuccessfully(): void
    {
        // Arrange
        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

        $this->createMinimalConfiguration();

        // Act && Assert
        $client->request('GET', '/');
        self::assertSelectorTextContains('h1', $translator->trans('home.welcome'));

        $client->clickLink($translator->trans('admin.titlePage'));

        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.titlePage'));

        $client->clickLink($translator->trans('admin.familyLog.titlePage'));

        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.familyLog.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.familyLog.titlePage'));

        $client->clickLink($translator->trans('admin.familyLog.create.titleShort'));

        $client->wait(1);
        self::assertSelectorTextContains('h1', $translator->trans('admin.familyLog.titlePage'));
        $client->waitForVisibility('turbo-frame#familyLog_create h3');
        self::assertSelectorTextContains(
            'turbo-frame#familyLog_create h3',
            $translator->trans('admin.familyLog.create.titlePage')
        );

        $client->waitForVisibility('button[type="submit"]');

        $familyLogLabel = 'Frais';

        $client->submitForm($translator->trans('add'), [
            'createFamilyLog[label]' => $familyLogLabel,
        ]);

        $client->wait(2);
        $getFamilyLogsUrl = $router->generate(GetFamilyLogsController::ROUTE_NAME);
        self::assertStringContainsString($getFamilyLogsUrl, $client->getCurrentURL());

        // Vérifier que le nouveau family log apparaît dans la liste
        $client->wait(1);
        self::assertSelectorTextContains('ul.table', $familyLogLabel);

        // Vérifier qu'on a quitté la page de création
        self::assertStringNotContainsString(
            $router->generate(CreateFamilyLogController::ROUTE_NAME),
            $client->getCurrentURL()
        );
    }

    public function testCancelDuringAnotherFamilyLogCreation(): void
    {
        // Arrange
        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

        $this->createMinimalConfiguration();

        // Act && Assert
        $client->request('GET', '/');
        self::assertSelectorTextContains('h1', $translator->trans('home.welcome'));

        $client->clickLink($translator->trans('admin.titlePage'));

        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.titlePage'));

        $client->clickLink($translator->trans('admin.familyLog.titlePage'));

        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.familyLog.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.familyLog.titlePage'));

        $client->clickLink($translator->trans('admin.familyLog.create.titleShort'));

        $client->wait(1);
        self::assertSelectorTextContains('h1', $translator->trans('admin.familyLog.titlePage'));
        $client->waitForVisibility('turbo-frame#familyLog_create h3');
        self::assertSelectorTextContains(
            'turbo-frame#familyLog_create h3',
            $translator->trans('admin.familyLog.create.titlePage')
        );

        $client->waitForVisibility('a[role="button"][aria-label="Cancel"]');

        $cancelButtonSelector = 'a[role="button"][aria-label="Cancel"]';
        self::assertSelectorExists($cancelButtonSelector);
        self::assertSelectorTextContains($cancelButtonSelector, $translator->trans('cancel'));

        $client->clickLink($translator->trans('cancel'));

        $client->wait(2);
        $getFamilyLogsUrl = $router->generate(GetFamilyLogsController::ROUTE_NAME);
        self::assertStringContainsString($getFamilyLogsUrl, $client->getCurrentURL());
        self::assertSelectorTextContains('h1', $translator->trans('admin.familyLog.titlePage'));
    }

    public function testCreateFamilyLogWithParentSuccessfully(): void
    {
        // Arrange
        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

        $config = $this->createMinimalConfiguration();
        $parentFamilyLog = $config['familyLog'];

        // Act && Assert
        $client->request('GET', '/');
        self::assertSelectorTextContains('h1', $translator->trans('home.welcome'));

        $client->clickLink($translator->trans('admin.titlePage'));

        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.titlePage'));

        $client->clickLink($translator->trans('admin.familyLog.titlePage'));

        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.familyLog.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.familyLog.titlePage'));

        $client->clickLink($translator->trans('admin.familyLog.create.titleShort'));

        $client->wait(1);
        self::assertSelectorTextContains('h1', $translator->trans('admin.familyLog.titlePage'));
        $client->waitForVisibility('turbo-frame#familyLog_create h3');
        self::assertSelectorTextContains(
            'turbo-frame#familyLog_create h3',
            $translator->trans('admin.familyLog.create.titlePage')
        );

        $client->waitForVisibility('button[type="submit"]');

        $familyLogLabel = 'Viande';

        $client->submitForm($translator->trans('add'), [
            'createFamilyLog[label]' => $familyLogLabel,
            'createFamilyLog[parent]' => $parentFamilyLog->uuid()->toString(),
        ]);

        $client->wait(2);
        $getFamilyLogsUrl = $router->generate(GetFamilyLogsController::ROUTE_NAME);
        self::assertStringContainsString($getFamilyLogsUrl, $client->getCurrentURL());

        // Vérifier que le nouveau family log apparaît dans la liste
        $client->wait(1);
        self::assertSelectorTextContains('ul.table', $familyLogLabel);

        // Vérifier que le parent est aussi affiché dans la liste
        self::assertSelectorTextContains('ul.table', 'Surgelé');

        // Vérifier qu'on a quitté la page de création
        self::assertStringNotContainsString(
            $router->generate(CreateFamilyLogController::ROUTE_NAME),
            $client->getCurrentURL()
        );
    }
}
