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

use Admin\Tests\DataBuilder\CompanyDataBuilder;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use Admin\Tests\DataBuilder\ZoneStorageDataBuilder;
use Admin\UseCases\Gateway\CompanyRepository;
use Admin\UseCases\Gateway\FamilyLogRepository;
use Admin\UseCases\Gateway\SupplierRepository;
use Admin\UseCases\Gateway\TaxRepository;
use Admin\UseCases\Gateway\UnitRepository;
use Admin\UseCases\Gateway\ZoneStorageRepository;
use App\Shared\Tests\BaseFunctionalTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group functionalTest
 */
final class ConfigurationControllerTest extends BaseFunctionalTestCase
{
    private const CONFIGURATION_URI = '/admin/configure';

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
        /** @var CompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(CompanyRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $company = (new CompanyDataBuilder())->create('TestCompany')->build();
        $companyRepository->save($company);

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
        /** @var CompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(CompanyRepository::class);

        /** @var UnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(UnitRepository::class);

        /** @var TaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(TaxRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $company = (new CompanyDataBuilder())->create('TestCompany')->build();
        $companyRepository->save($company);
        $unit = (new UnitDataBuilder())->create('Kilogramme', 'KG')->build();
        $unitRepository->save($unit);
        $tax = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $taxRepository->save($tax);

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
        /** @var CompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(CompanyRepository::class);

        /** @var UnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(UnitRepository::class);

        /** @var TaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(TaxRepository::class);

        /** @var FamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(FamilyLogRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $company = (new CompanyDataBuilder())->create('TestCompany')->build();
        $companyRepository->save($company);
        $unit = (new UnitDataBuilder())->create('Kilogramme', 'KG')->build();
        $unitRepository->save($unit);
        $tax = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $taxRepository->save($tax);
        $familyLog = (new FamilyLogDataBuilder())->create('Frais')->build();
        $familyLogRepository->save($familyLog);

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
        /** @var CompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(CompanyRepository::class);

        /** @var UnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(UnitRepository::class);

        /** @var TaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(TaxRepository::class);

        /** @var FamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(FamilyLogRepository::class);

        /** @var ZoneStorageRepository $zoneStorageRepository */
        $zoneStorageRepository = self::getContainer()->get(ZoneStorageRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $company = (new CompanyDataBuilder())->create('TestCompany')->build();
        $companyRepository->save($company);
        $unit = (new UnitDataBuilder())->create('Kilogramme', 'KG')->build();
        $unitRepository->save($unit);
        $tax = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $taxRepository->save($tax);
        $familyLog = (new FamilyLogDataBuilder())->create('Frais')->build();
        $familyLogRepository->save($familyLog);
        $zoneStorage = (new ZoneStorageDataBuilder())->create('Réserve froide', $familyLog)->build();
        $zoneStorageRepository->save($zoneStorage);

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
        /** @var CompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(CompanyRepository::class);

        /** @var UnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(UnitRepository::class);

        /** @var TaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(TaxRepository::class);

        /** @var FamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(FamilyLogRepository::class);

        /** @var ZoneStorageRepository $zoneStorageRepository */
        $zoneStorageRepository = self::getContainer()->get(ZoneStorageRepository::class);

        /** @var SupplierRepository $supplierRepository */
        $supplierRepository = self::getContainer()->get(SupplierRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $company = (new CompanyDataBuilder())->create('TestCompany')->build();
        $companyRepository->save($company);
        $unit = (new UnitDataBuilder())->create('Kilogramme', 'KG')->build();
        $unitRepository->save($unit);
        $tax = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $taxRepository->save($tax);
        $familyLog = (new FamilyLogDataBuilder())->create('Frais')->build();
        $familyLogRepository->save($familyLog);
        $zoneStorage = (new ZoneStorageDataBuilder())->create('Réserve froide', $familyLog)->build();
        $zoneStorageRepository->save($zoneStorage);
        $supplier = (new SupplierDataBuilder())->create('Supplier 1', $familyLog)->build();
        $supplierRepository->save($supplier);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CONFIGURATION_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.configuration.titlePage'));

        $list = $crawler->filter('div.row > aside.col-md-3 > nav#menu > ul');

        self::assertCount(6, $list->children('li > a.w100'));
        self::assertCount(5, $list->children('li > a.disable-link'));
    }
}
