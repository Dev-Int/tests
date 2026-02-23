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

namespace Admin\Tests\EndToEnd\Company;

use Admin\Contracts\Services\Provider\ConfigurationServiceProvider;
use Shared\Tests\AuthenticatedPantherTestTrait;
use Shared\Tests\BasePantherTestCase;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group e2eTest
 */
final class UpdateACompanyTest extends BasePantherTestCase
{
    use AuthenticatedPantherTestTrait;

    public function testUpdateACompanySuccessfully(): void
    {
        // Arrange
        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);
        $this->createMinimalConfiguration();

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $this->loginViaForm($client, $translator);

        /** @var ConfigurationServiceProvider $configureService */
        $configureService = self::getContainer()->get(ConfigurationServiceProvider::class);

        $isConfigured = $configureService->isApplicationReady();
        self::assertTrue($isConfigured);

        // Act && Assert
        $client->request('GET', '/');
        $client->clickLink($translator->trans('admin.titlePage'));
        $client->waitForElementToContain('h1', $translator->trans('admin.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.titlePage'));

        $client->clickLink($translator->trans('admin.company.titlePage'));
        $client->waitForElementToContain('h1', $translator->trans('admin.company.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.company.titlePage'));

        $client->clickLink($translator->trans(
            'admin.company.update.titleShort',
            ['%companyName%' => 'Dev-Int Création']
        ));
        $client->waitForElementToContain('h3', 'Modifier');

        // Assert - h1 from index.html.twig (should stay on the index page with Turbo Frame)
        self::assertSelectorTextContains('h1', $translator->trans('admin.company.titlePage'));

        self::assertSelectorTextContains('h3', $translator->trans(
            'admin.company.update.titleShort',
            ['%companyName%' => 'Dev-Int Création']
        ));
    }
}
