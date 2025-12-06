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

namespace Admin\Tests\EndToEnd\Supplier;

use Admin\Adapters\Controller\Symfony\Controller\Supplier\CreateSupplier\CreateSupplierController;
use Admin\Adapters\Controller\Symfony\Controller\Supplier\GetSuppliers\GetSuppliersController;
use App\Shared\Tests\BasePantherTestCase;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group e2eTest
 */
final class CreateAnotherSupplierTest extends BasePantherTestCase
{
    public function testCreateAnotherSupplierSuccessfully(): void
    {
        // Arrange
        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

        $config = $this->createMinimalConfiguration();
        $familyLog = $config['familyLog'];

        // Act && Assert
        $client->request('GET', '/');
        self::assertSelectorTextContains('h1', $translator->trans('home.welcome'));

        $client->clickLink($translator->trans('admin.titlePage'));

        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.titlePage'));

        $client->clickLink($translator->trans('admin.supplier.titlePage'));

        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.supplier.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.supplier.titlePage'));

        $client->clickLink($translator->trans('admin.supplier.create.titleShort'));

        $client->wait(1);
        self::assertSelectorTextContains('h1', $translator->trans('admin.supplier.titlePage'));
        $client->waitForVisibility('turbo-frame#supplier_create h3');
        self::assertSelectorTextContains(
            'turbo-frame#supplier_create h3',
            $translator->trans('admin.supplier.create.titlePage')
        );

        $client->waitForVisibility('button[type="submit"]');

        $supplierName = 'Nouveau Fournisseur';

        $client->submitForm($translator->trans('add'), [
            'createSupplier[name]' => $supplierName,
            'createSupplier[address]' => '10 avenue des Champs',
            'createSupplier[postalCode]' => '69000',
            'createSupplier[city]' => 'Lyon',
            'createSupplier[country]' => 'France',
            'createSupplier[phone]' => '0478123456',
            'createSupplier[email]' => 'nouveau@fournisseur.fr',
            'createSupplier[contact]' => 'Marie Martin',
            'createSupplier[cellphone]' => '0698765432',
            'createSupplier[familyLog]' => $familyLog->uuid()->toString(),
            'createSupplier[delayDelivery]' => '3',
            'createSupplier[orderDays]' => [1, 3],  // Mardi, Jeudi
        ]);

        $client->wait(2);
        $getSuppliersUrl = $router->generate(GetSuppliersController::ROUTE_NAME);
        self::assertStringContainsString($getSuppliersUrl, $client->getCurrentURL());

        $client->wait(1);
        self::assertSelectorTextContains('ul.table', $supplierName);

        // Vérifier qu'on a quitté la page de création
        self::assertStringNotContainsString(
            $router->generate(CreateSupplierController::ROUTE_NAME),
            $client->getCurrentURL()
        );
    }

    public function testCancelDuringAnotherSupplierCreation(): void
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

        $client->clickLink($translator->trans('admin.supplier.titlePage'));

        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.supplier.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.supplier.titlePage'));

        $client->clickLink($translator->trans('admin.supplier.create.titleShort'));

        $client->wait(1);
        self::assertSelectorTextContains('h1', $translator->trans('admin.supplier.titlePage'));
        $client->waitForVisibility('turbo-frame#supplier_create h3');
        self::assertSelectorTextContains(
            'turbo-frame#supplier_create h3',
            $translator->trans('admin.supplier.create.titlePage')
        );

        $client->waitForVisibility('a[role="button"][aria-label="Cancel"]');

        $cancelButtonSelector = 'a[role="button"][aria-label="Cancel"]';
        self::assertSelectorExists($cancelButtonSelector);
        self::assertSelectorTextContains($cancelButtonSelector, $translator->trans('cancel'));

        $client->clickLink($translator->trans('cancel'));

        $client->wait(2);
        $getSuppliersUrl = $router->generate(GetSuppliersController::ROUTE_NAME);
        self::assertStringContainsString($getSuppliersUrl, $client->getCurrentURL());
        self::assertSelectorTextContains('h1', $translator->trans('admin.supplier.titlePage'));
    }
}
