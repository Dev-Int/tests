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

use Admin\Adapters\Gateway\ConfigurationService;
use Shared\Tests\BasePantherTestCase;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group e2eTest
 */
final class UpdateACompanyTest extends BasePantherTestCase
{
    public function testUpdateACompanySuccessfully(): void
    {
        // Arrange
        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);
        $this->createMinimalConfiguration();

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        /** @var ConfigurationService $configureService */
        $configureService = self::getContainer()->get(ConfigurationService::class);

        $isConfigured = $configureService->isConfigured();
        self::assertTrue($isConfigured);

        // Act && Assert
        $client->request('GET', '/');
        $client->clickLink($translator->trans('admin.titlePage'));

        // Wait for Turbo to initialize
        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.titlePage'));

        $client->clickLink($translator->trans('admin.company.titlePage'));

        // Wait for Turbo to initialize
        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.company.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.company.titlePage'));

        $client->clickLink($translator->trans(
            'admin.company.update.titleShort',
            ['%companyName%' => 'Dev-Int Création']
        ));

        // Wait for Turbo Frame to update (should stay on the same page)
        $client->wait(1);
        $client->waitForElementToContain('h3', 'Modifier');

        // Assert - h1 from index.html.twig (should stay on the index page with Turbo Frame)
        self::assertSelectorTextContains('h1', $translator->trans('admin.company.titlePage'));

        self::assertSelectorTextContains('h3', $translator->trans(
            'admin.company.update.titleShort',
            ['%companyName%' => 'Dev-Int Création']
        ));
    }
}
