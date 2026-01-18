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

use Admin\Tests\Factory\CompanyFactory;
use Admin\Tests\Factory\FamilyLogFactory;
use Admin\Tests\Factory\SupplierFactory;
use Admin\Tests\Factory\TaxFactory;
use Admin\Tests\Factory\UnitFactory;
use Admin\Tests\Factory\ZoneStorageFactory;
use Shared\Tests\BaseFunctionalTestCase;
use Shared\Tests\RedirectsToLoginTestTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 */
final class ConfigurationControllerTest extends BaseFunctionalTestCase
{
    use Factories;
    use RedirectsToLoginTestTrait;

    private const string CONFIGURATION_URI = '/admin/configure';

    public function testDeniesAccessToRoleUser(): void
    {
        $this->logoutUser();
        $this->authenticateAsRoleUser();
        $this->client->catchExceptions(false);
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Access Denied.');
        $this->client->request(Request::METHOD_GET, self::CONFIGURATION_URI);
    }

    public function testConfigurePageWillSucceed(): void
    {
        // Arrange && Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CONFIGURATION_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Configuration');

        $list = $crawler->filter('div.row > aside.col-md-3 > nav#menu > ul');

        self::assertCount(6, $list->children('li > a.w100'));
        self::assertCount(0, $list->children('li > a.disable-link'));
    }

    public function testAdminPageWillRedirectToConfigurePage(): void
    {
        // Arrange && Act
        $this->client->request(Request::METHOD_GET, '/admin/');

        // Assert
        self::assertResponseRedirects(self::CONFIGURATION_URI);
        $crawler = $this->client->followRedirect();

        $list = $crawler->filter('div.row > aside.col-md-3 > nav#menu > ul');

        self::assertCount(6, $list->children('li > a.w100'));
        self::assertCount(0, $list->children('li > a.disable-link'));
    }

    public function testConfigurePageWillGoHomePage(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CONFIGURATION_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.configuration.titlePage'));

        $list = $crawler->filter('div.row > aside.col-md-3 > nav#menu > ul');

        self::assertCount(6, $list->children('li > a.w100'));
        self::assertCount(0, $list->children('li > a.disable-link'));

        $home = $crawler->selectLink($translator->trans('admin.returnButton'))->link(Request::METHOD_GET);
        $this->client->click($home);
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('home.welcome'));
    }

    public function testConfigurePageWithCompany(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        CompanyFactory::createOne(['name' => 'TestCompany']);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CONFIGURATION_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.configuration.titlePage'));

        $list = $crawler->filter('div.row > aside.col-md-3 > nav#menu > ul');

        self::assertCount(6, $list->children('li > a.w100'));
        self::assertCount(1, $list->children('li > a.disable-link'));
    }

    public function testConfigurePageWithApplicationConfigured(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        CompanyFactory::createOne(['name' => 'TestCompany']);
        UnitFactory::createOne(['label' => 'Kilogramme', 'abbreviation' => 'KG']);
        TaxFactory::createOne(['name' => 'TVA taux normal', 'rate' => 20.0]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CONFIGURATION_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.configuration.titlePage'));

        $list = $crawler->filter('div.row > aside.col-md-3 > nav#menu > ul');

        self::assertCount(6, $list->children('li > a.w100'));
        self::assertCount(2, $list->children('li > a.disable-link'));
    }

    public function testConfigurePageWithFamilyLog(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        CompanyFactory::createOne(['name' => 'TestCompany']);
        UnitFactory::createOne(['label' => 'Kilogramme', 'abbreviation' => 'KG']);
        TaxFactory::createOne(['name' => 'TVA taux normal', 'rate' => 20.0]);
        FamilyLogFactory::createOne(['label' => 'Frais']);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CONFIGURATION_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.configuration.titlePage'));

        $list = $crawler->filter('div.row > aside.col-md-3 > nav#menu > ul');

        self::assertCount(6, $list->children('li > a.w100'));
        self::assertCount(3, $list->children('li > a.disable-link'));
    }

    public function testConfigurePageWithZoneStorage(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        CompanyFactory::createOne(['name' => 'TestCompany']);
        UnitFactory::createOne(['label' => 'Kilogramme', 'abbreviation' => 'KG']);
        TaxFactory::createOne(['name' => 'TVA taux normal', 'rate' => 20.0]);
        $familyLog = FamilyLogFactory::createOne(['label' => 'Frais']);
        ZoneStorageFactory::createOne([
            'label' => 'Réserve froide',
            'familyLog' => $familyLog->_real(),
        ]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CONFIGURATION_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.configuration.titlePage'));

        $list = $crawler->filter('div.row > aside.col-md-3 > nav#menu > ul');

        self::assertCount(6, $list->children('li > a.w100'));
        self::assertCount(4, $list->children('li > a.disable-link'));
    }

    public function testConfigurePageWithSupplier(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        CompanyFactory::createOne(['name' => 'TestCompany']);
        UnitFactory::createOne(['label' => 'Kilogramme', 'abbreviation' => 'KG']);
        TaxFactory::createOne(['name' => 'TVA taux normal', 'rate' => 20.0]);
        $familyLog = FamilyLogFactory::createOne(['label' => 'Frais']);
        ZoneStorageFactory::createOne([
            'label' => 'Réserve froide',
            'familyLog' => $familyLog->_real(),
        ]);
        SupplierFactory::createOne([
            'name' => 'Supplier 1',
            'familyLog' => $familyLog->_real(),
        ]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CONFIGURATION_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.configuration.titlePage'));

        $list = $crawler->filter('div.row > aside.col-md-3 > nav#menu > ul');

        self::assertCount(6, $list->children('li > a.w100'));
        self::assertCount(5, $list->children('li > a.disable-link'));
    }

    protected function getProtectedUri(): string
    {
        return self::CONFIGURATION_URI;
    }
}
