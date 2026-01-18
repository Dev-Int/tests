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
use Shared\Tests\BaseFunctionalTestCase;
use Shared\Tests\RedirectsToLoginTestTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 */
final class HomeControllerTest extends BaseFunctionalTestCase
{
    use Factories;
    use RedirectsToLoginTestTrait;

    private const string HOME_URI = '/admin/';

    public function testHomePageWillSucceed(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        CompanyFactory::createOne(['name' => 'TestCompany']);
        $unit = UnitFactory::createOne(['label' => 'Kilogramme', 'abbreviation' => 'kg']);
        $tax = TaxFactory::createOne(['name' => 'TVA taux normal', 'rate' => 20.0]);
        $familyLog = FamilyLogFactory::createOne(['label' => 'Surgelé']);
        $zoneStorage = ZoneStorageFactory::createOne([
            'label' => 'Réserve négative',
            'familyLog' => $familyLog->_real(),
        ]);
        $supplier = SupplierFactory::createOne([
            'name' => 'supplier 1',
            'familyLog' => $familyLog->_real(),
        ]);
        ArticleFactory::createOne([
            'name' => 'article 1',
            'supplier' => $supplier->_real(),
            'tax' => $tax->_real(),
            'zoneStorages' => [$zoneStorage->_real()],
            'familyLog' => $familyLog->_real(),
            'packaging' => [[$unit->_real()->toDomain(), 1.0], null, null],
        ]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::HOME_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.titlePage'));
        $brand = $crawler->filter('body > header > nav')->children('ul')->first();
        self::assertSame('Application', $brand->text());
    }

    protected function getProtectedUri(): string
    {
        return self::HOME_URI;
    }
}
