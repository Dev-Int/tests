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

namespace Admin\Tests\EndToEnd\Tax;

use Admin\Adapters\Controller\Symfony\Controller\Tax\CreateTax\CreateTaxController;
use Admin\Adapters\Controller\Symfony\Controller\Tax\GetTaxes\GetTaxesController;
use Shared\Tests\AuthenticatedPantherTestTrait;
use Shared\Tests\BasePantherTestCase;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group e2eTest
 */
final class CreateAnotherTaxTest extends BasePantherTestCase
{
    use AuthenticatedPantherTestTrait;

    public function testCreateAnotherTaxSuccessfully(): void
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

        $client->clickLink($translator->trans('admin.tax.titlePage'));
        $client->waitForElementToContain('h1', $translator->trans('admin.tax.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.tax.titlePage'));

        $client->clickLink($translator->trans('admin.tax.create.titleShort'));
        $client->waitForVisibility('turbo-frame#tax_create h3');
        self::assertSelectorTextContains('h1', $translator->trans('admin.tax.titlePage'));
        self::assertSelectorTextContains(
            'turbo-frame#tax_create h3',
            $translator->trans('admin.tax.create.titlePage')
        );

        $client->waitForVisibility('button[type="submit"]');

        $taxName = 'TVA intermédiaire';
        $taxRate = 0.1; // 10%

        $client->submitForm($translator->trans('add'), [
            'createTax[name]' => $taxName,
            'createTax[rate]' => $taxRate,
        ]);

        $client->waitForVisibility('ul.table');
        $getTaxesUrl = $router->generate(GetTaxesController::ROUTE_NAME);
        self::assertStringContainsString($getTaxesUrl, $client->getCurrentURL());
        self::assertSelectorTextContains('ul.table', $taxName);

        self::assertStringNotContainsString(
            $router->generate(CreateTaxController::ROUTE_NAME),
            $client->getCurrentURL()
        );
    }

    public function testCancelDuringAnotherTaxCreation(): void
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

        $client->clickLink($translator->trans('admin.tax.titlePage'));
        $client->waitForElementToContain('h1', $translator->trans('admin.tax.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.tax.titlePage'));

        $client->clickLink($translator->trans('admin.tax.create.titleShort'));
        $client->waitForVisibility('turbo-frame#tax_create h3');
        self::assertSelectorTextContains('h1', $translator->trans('admin.tax.titlePage'));
        self::assertSelectorTextContains(
            'turbo-frame#tax_create h3',
            $translator->trans('admin.tax.create.titlePage')
        );

        $client->waitForVisibility('a[role="button"][aria-label="Cancel"]');

        $cancelButtonSelector = 'a[role="button"][aria-label="Cancel"]';
        self::assertSelectorExists($cancelButtonSelector);
        self::assertSelectorTextContains($cancelButtonSelector, $translator->trans('cancel'));

        $client->clickLink($translator->trans('cancel'));
        $client->waitForElementToContain('h1', $translator->trans('admin.tax.titlePage'));
        $getTaxesUrl = $router->generate(GetTaxesController::ROUTE_NAME);
        self::assertStringContainsString($getTaxesUrl, $client->getCurrentURL());
        self::assertSelectorTextContains('h1', $translator->trans('admin.tax.titlePage'));
    }
}
