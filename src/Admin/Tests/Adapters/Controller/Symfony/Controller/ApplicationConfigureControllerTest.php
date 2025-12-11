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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller;

use Admin\Tests\Factory\ArticleFactory;
use Admin\Tests\Factory\CompanyFactory;
use Admin\Tests\Factory\FamilyLogFactory;
use Admin\Tests\Factory\SupplierFactory;
use Admin\Tests\Factory\TaxFactory;
use Admin\Tests\Factory\UnitFactory;
use Admin\Tests\Factory\ZoneStorageFactory;
use App\Shared\Tests\BaseFunctionalTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 */
final class ApplicationConfigureControllerTest extends BaseFunctionalTestCase
{
    use Factories;

    private const APPLICATION_CONFIGURE_URI = '/admin/configure/application';

    public function testApplicationConfigurePageWillSucceed(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        CompanyFactory::createOne(['name' => 'TestCompany']);

        // Act
        $this->client->request(Request::METHOD_GET, self::APPLICATION_CONFIGURE_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.configuration.application.titlePage'));
    }

    public function testApplicationConfigured(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        CompanyFactory::createOne(['name' => 'Test company']);
        $colis = UnitFactory::createOne(['label' => 'Colis', 'abbreviation' => 'kg']);
        $tax = TaxFactory::createOne(['name' => 'TVA taux réduit', 'rate' => 5.5]);
        $familyLog = FamilyLogFactory::createOne(['label' => 'Frais']);
        $zoneStorage = ZoneStorageFactory::createOne([
            'label' => 'Réserve froide',
            'familyLog' => $familyLog->_real(),
        ]);
        $supplier = SupplierFactory::createOne([
            'name' => 'Supplier 1',
            'familyLog' => $familyLog->_real(),
        ]);
        ArticleFactory::createOne([
            'name' => 'Article 1',
            'supplier' => $supplier->_real(),
            'tax' => $tax->_real(),
            'zoneStorages' => [$zoneStorage->_real()],
            'familyLog' => $familyLog->_real(),
            'packaging' => [[$colis->_real()->toDomain(), 1.0], null, null],
        ]);

        // Act
        $this->client->request(Request::METHOD_GET, self::APPLICATION_CONFIGURE_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.configuration.application.titlePage'));
    }

    public function testApplicationConfigurePageRedirectConfigurePage(): void
    {
        // Arrange && Act
        $this->client->request(Request::METHOD_GET, self::APPLICATION_CONFIGURE_URI);

        // Assert
        self::assertResponseRedirects('/admin/configure');
    }
}
