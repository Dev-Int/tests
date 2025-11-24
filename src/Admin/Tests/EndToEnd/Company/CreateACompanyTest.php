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

use Admin\Adapters\Controller\Symfony\Controller\Company\CreateCompany\CreateCompanyController;
use App\Shared\Tests\BasePantherTestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group e2eTest
 */
final class CreateACompanyTest extends BasePantherTestCase
{
    public function testCreateACompanySuccessfully(): void
    {
        // Arrange
        $client = self::createPantherClient();

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        // Act
        $client->request('GET', '/');
        self::assertSelectorTextContains('h1', $translator->trans('home.welcome'));

        $client->clickLink($translator->trans('admin.titlePage'));
        // Wait for Turbo to initialize
        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.configuration.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.configuration.titlePage'));

        $client->clickLink($translator->trans('admin.company.create.titleShort'));
        // Wait for Turbo to initialize
        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.company.create.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.company.create.titlePage'));

        $client->waitForVisibility('button[type="submit"]');

        $client->submitForm($translator->trans('add'), [
            'createCompany[name]' => 'Dev-Int Création',
            'createCompany[address]' => '5, rue des Plantes',
            'createCompany[postalCode]' => '75000',
            'createCompany[city]' => 'Paris',
            'createCompany[country]' => 'France',
            'createCompany[phone]' => '+33297000000',
            'createCompany[email]' => 'test@test.fr',
            'createCompany[contact]' => 'Laurent',
        ]);

        // Wait for the redirect to complete
        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.configuration.titlePage'));

        // Assert - should be redirected to the configuration page after successful creation
        self::assertSelectorTextContains('h1', $translator->trans('admin.configuration.titlePage'));

        // Assert - the Company menu button should now be disabled (company created successfully)
        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');
        $companyCreateUrl = $router->generate(CreateCompanyController::ROUTE_NAME);
        $companyButtonSelector = \sprintf(
            '#menu > ul > li > a[href="%s"].secondary.disable-link',
            $companyCreateUrl
        );
        self::assertSelectorExists($companyButtonSelector);
        self::assertSelectorTextContains(
            $companyButtonSelector,
            $translator->trans('admin.company.create.titleShort')
        );
    }

    public function testCreateACompanyCancelledDuringSeizure(): void
    {
        // Arrange
        $client = self::createPantherClient();

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        // Act
        $client->request('GET', '/');
        self::assertSelectorTextContains('h1', $translator->trans('home.welcome'));

        $client->clickLink($translator->trans('admin.titlePage'));
        // Wait for Turbo to initialize
        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.configuration.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.configuration.titlePage'));

        $client->clickLink($translator->trans('admin.company.create.titleShort'));
        // Wait for Turbo to initialize
        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.company.create.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.company.create.titlePage'));

        $client->waitForVisibility('a[role="button"][aria-label="Cancel"]');

        $client->clickLink($translator->trans('cancel'));

        // Wait for the navigation to complete
        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.configuration.titlePage'));

        // Assert - should be back to the configuration page
        self::assertSelectorTextContains('h1', $translator->trans('admin.configuration.titlePage'));

        // Assert - the menu button should still be active (configuration not completes yet)
        // The button should NOT have the "disable-link" class since no company was created
        self::assertSelectorNotExists('#menu > ul > li > a.disable-link');
    }
}
