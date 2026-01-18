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

namespace Admin\Tests\EndToEnd\ZoneStorage;

use Admin\Adapters\Controller\Symfony\Controller\ZoneStorage\CreateZoneStorage\CreateZoneStorageController;
use Admin\Adapters\Controller\Symfony\Controller\ZoneStorage\GetZoneStorages\GetZoneStoragesController;
use Shared\Tests\AuthenticatedPantherTestTrait;
use Shared\Tests\BasePantherTestCase;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group e2eTest
 */
final class CreateAnotherZoneStorageTest extends BasePantherTestCase
{
    use AuthenticatedPantherTestTrait;

    public function testCreateAnotherZoneStorageSuccessfully(): void
    {
        // Arrange
        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

        $this->loginViaForm($client, $translator);

        $config = $this->createMinimalConfiguration();
        $familyLog = $config['familyLog'];

        // Act && Assert
        $client->request('GET', '/');
        self::assertSelectorTextContains('h1', $translator->trans('home.welcome'));

        $client->clickLink($translator->trans('admin.titlePage'));
        $client->waitForElementToContain('h1', $translator->trans('admin.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.titlePage'));

        $client->clickLink($translator->trans('admin.zoneStorage.titlePage'));
        $client->waitForElementToContain('h1', $translator->trans('admin.zoneStorage.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.zoneStorage.titlePage'));

        $client->clickLink($translator->trans('admin.zoneStorage.create.titleShort'));
        $client->waitForVisibility('turbo-frame#zoneStorage_create h3');
        self::assertSelectorTextContains('h1', $translator->trans('admin.zoneStorage.titlePage'));
        self::assertSelectorTextContains(
            'turbo-frame#zoneStorage_create h3',
            $translator->trans('admin.zoneStorage.create.titlePage')
        );

        $client->waitForVisibility('button[type="submit"]');

        $zoneStorageLabel = 'Réserve sèche';

        $client->submitForm($translator->trans('add'), [
            'createZoneStorage[label]' => $zoneStorageLabel,
            'createZoneStorage[familyLog]' => $familyLog->uuid()->toString(),
        ]);

        $client->waitForVisibility('ul.table');
        $getZoneStoragesUrl = $router->generate(GetZoneStoragesController::ROUTE_NAME);
        self::assertStringContainsString($getZoneStoragesUrl, $client->getCurrentURL());
        self::assertSelectorTextContains('ul.table', $zoneStorageLabel);

        // Vérifier qu'on a quitté la page de création
        self::assertStringNotContainsString(
            $router->generate(CreateZoneStorageController::ROUTE_NAME),
            $client->getCurrentURL()
        );
    }

    public function testCancelDuringAnotherZoneStorageCreation(): void
    {
        // Arrange
        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

        $this->loginViaForm($client, $translator);

        $this->createMinimalConfiguration();

        // Act && Assert
        $client->request('GET', '/');
        self::assertSelectorTextContains('h1', $translator->trans('home.welcome'));

        $client->clickLink($translator->trans('admin.titlePage'));
        $client->waitForElementToContain('h1', $translator->trans('admin.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.titlePage'));

        $client->clickLink($translator->trans('admin.zoneStorage.titlePage'));
        $client->waitForElementToContain('h1', $translator->trans('admin.zoneStorage.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.zoneStorage.titlePage'));

        $client->clickLink($translator->trans('admin.zoneStorage.create.titleShort'));
        $client->waitForVisibility('turbo-frame#zoneStorage_create h3');
        self::assertSelectorTextContains('h1', $translator->trans('admin.zoneStorage.titlePage'));
        self::assertSelectorTextContains(
            'turbo-frame#zoneStorage_create h3',
            $translator->trans('admin.zoneStorage.create.titlePage')
        );

        $client->waitForVisibility('a[role="button"][aria-label="Cancel"]');

        $cancelButtonSelector = 'a[role="button"][aria-label="Cancel"]';
        self::assertSelectorExists($cancelButtonSelector);
        self::assertSelectorTextContains($cancelButtonSelector, $translator->trans('cancel'));

        $client->clickLink($translator->trans('cancel'));
        $client->waitForElementToContain('h1', $translator->trans('admin.zoneStorage.titlePage'));
        $getZoneStoragesUrl = $router->generate(GetZoneStoragesController::ROUTE_NAME);
        self::assertStringContainsString($getZoneStoragesUrl, $client->getCurrentURL());
        self::assertSelectorTextContains('h1', $translator->trans('admin.zoneStorage.titlePage'));
    }
}
