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

namespace Admin\Tests\EndToEnd\Unit;

use Admin\Adapters\Controller\Symfony\Controller\Unit\CreateUnit\CreateUnitController;
use Admin\Adapters\Controller\Symfony\Controller\Unit\GetUnits\GetUnitsController;
use Shared\Tests\BasePantherTestCase;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group e2eTest
 */
final class CreateAnotherUnitTest extends BasePantherTestCase
{
    public function testCreateAnotherUnitSuccessfully(): void
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

        $client->clickLink($translator->trans('admin.unit.titlePage'));

        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.unit.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.unit.titlePage'));

        $client->clickLink($translator->trans('admin.unit.create.titleShort'));

        $client->wait(1);
        self::assertSelectorTextContains('h1', $translator->trans('admin.unit.titlePage'));
        $client->waitForVisibility('turbo-frame#unit_create h3');
        self::assertSelectorTextContains(
            'turbo-frame#unit_create h3',
            $translator->trans('admin.unit.create.titlePage')
        );

        $client->waitForVisibility('button[type="submit"]');

        $unitLabel = 'Litre';
        $unitAbbreviation = 'L';

        $client->submitForm($translator->trans('add'), [
            'createUnit[label]' => $unitLabel,
            'createUnit[abbreviation]' => $unitAbbreviation,
        ]);

        $client->wait(2);
        $getUnitsUrl = $router->generate(GetUnitsController::ROUTE_NAME);
        self::assertStringContainsString($getUnitsUrl, $client->getCurrentURL());

        $client->wait(1);
        self::assertSelectorTextContains('ul.table', $unitLabel);

        self::assertStringNotContainsString(
            $router->generate(CreateUnitController::ROUTE_NAME),
            $client->getCurrentURL()
        );
    }

    public function testCancelDuringAnotherUnitCreation(): void
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

        $client->clickLink($translator->trans('admin.unit.titlePage'));

        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.unit.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.unit.titlePage'));

        $client->clickLink($translator->trans('admin.unit.create.titleShort'));

        $client->wait(1);
        self::assertSelectorTextContains('h1', $translator->trans('admin.unit.titlePage'));
        $client->waitForVisibility('turbo-frame#unit_create h3');
        self::assertSelectorTextContains(
            'turbo-frame#unit_create h3',
            $translator->trans('admin.unit.create.titlePage')
        );

        $client->waitForVisibility('a[role="button"][aria-label="Cancel"]');

        $cancelButtonSelector = 'a[role="button"][aria-label="Cancel"]';
        self::assertSelectorExists($cancelButtonSelector);
        self::assertSelectorTextContains($cancelButtonSelector, $translator->trans('cancel'));

        $client->clickLink($translator->trans('cancel'));

        $client->wait(2);
        $getUnitsUrl = $router->generate(GetUnitsController::ROUTE_NAME);
        self::assertStringContainsString($getUnitsUrl, $client->getCurrentURL());
        self::assertSelectorTextContains('h1', $translator->trans('admin.unit.titlePage'));
    }
}
