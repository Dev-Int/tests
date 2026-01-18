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

use Admin\Adapters\Controller\Symfony\Controller\Supplier\GetSuppliers\GetSuppliersController;
use Shared\Tests\AuthenticatedPantherTestTrait;
use Shared\Tests\BasePantherTestCase;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group e2eTest
 */
final class RenameSupplierCancelTest extends BasePantherTestCase
{
    use AuthenticatedPantherTestTrait;

    public function testCancelDuringSupplierRename(): void
    {
        // Arrange
        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

        $this->loginViaForm($client, $translator);

        $config = $this->createMinimalConfiguration();
        $supplier = $config['supplier'];

        // Act && Assert
        $client->request('GET', '/');
        self::assertSelectorTextContains('h1', $translator->trans('home.welcome'));

        $client->clickLink($translator->trans('admin.titlePage'));
        $client->waitForElementToContain('h1', $translator->trans('admin.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.titlePage'));

        $client->clickLink($translator->trans('admin.supplier.titlePage'));
        $client->waitForElementToContain('h1', $translator->trans('admin.supplier.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.supplier.titlePage'));

        // Cliquer sur le bouton "Renommer" du premier fournisseur
        $renameButtonText = $translator->trans('admin.supplier.rename.button');
        $client->clickLink($renameButtonText);
        $client->waitForVisibility(\sprintf('turbo-frame#supplier_%s h3', $supplier->uuid()->toString()));
        self::assertSelectorTextContains('h1', $translator->trans('admin.supplier.titlePage'));
        self::assertSelectorTextContains(
            \sprintf('turbo-frame#supplier_%s h3', $supplier->uuid()->toString()),
            $translator->trans('admin.supplier.rename.titlePage', ['%supplierName%' => $supplier->name()->toString()])
        );

        $client->waitForVisibility('a[role="button"][aria-label="Cancel"]');

        $cancelButtonSelector = 'a[role="button"][aria-label="Cancel"]';
        self::assertSelectorExists($cancelButtonSelector);
        self::assertSelectorTextContains($cancelButtonSelector, $translator->trans('cancel'));

        $client->clickLink($translator->trans('cancel'));
        $client->waitForElementToContain('h1', $translator->trans('admin.supplier.titlePage'));
        $getSuppliersUrl = $router->generate(GetSuppliersController::ROUTE_NAME);
        self::assertStringContainsString($getSuppliersUrl, $client->getCurrentURL());
        self::assertSelectorTextContains('h1', $translator->trans('admin.supplier.titlePage'));
    }
}
