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

use Admin\Adapters\Gateway\ORM\Repository\DoctrineCompanyRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineSupplierRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineTaxRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineUnitRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineZoneStorageRepository;
use Admin\Tests\DataBuilder\CompanyDataBuilder;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use Admin\Tests\DataBuilder\ZoneStorageDataBuilder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functionalTest
 */
final class ConfigurationControllerTest extends WebTestCase
{
    private const CONFIGURATION_URI = '/admin/configure';

    public function testConfigurePageWillSucceed(): void
    {
        // Arrange
        $client = self::createClient();

        // Act
        $crawler = $client->request(Request::METHOD_GET, self::CONFIGURATION_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Configuration');

        $list = $crawler->filter('div.row > aside.col-md-3 > nav#menu > ul');

        self::assertCount(6, $list->children('li > a.w100'));
        self::assertCount(0, $list->children('li > a.disable-link'));
    }

    public function testAdminPageWillRedirectToConfigurePage(): void
    {
        // Arrange
        $client = self::createClient();

        // Act
        $client->request(Request::METHOD_GET, '/admin/');

        // Assert
        self::assertResponseRedirects(self::CONFIGURATION_URI);
        $crawler = $client->followRedirect();

        $list = $crawler->filter('div.row > aside.col-md-3 > nav#menu > ul');

        self::assertCount(6, $list->children('li > a.w100'));
        self::assertCount(0, $list->children('li > a.disable-link'));
    }

    public function testConfigurePageWillGoHomePage(): void
    {
        // Arrange
        $client = self::createClient();

        // Act
        $crawler = $client->request(Request::METHOD_GET, self::CONFIGURATION_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Configuration');

        $list = $crawler->filter('div.row > aside.col-md-3 > nav#menu > ul');

        self::assertCount(6, $list->children('li > a.w100'));
        self::assertCount(0, $list->children('li > a.disable-link'));

        $home = $crawler->selectLink('Retour à l\'accueil')->link(Request::METHOD_GET);
        $client->click($home);
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Welcome to your restaurant inventory management application!');
    }

    public function testConfigurePageWithCompany(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);

        $company = (new CompanyDataBuilder())->create('TestCompany')->build();
        $companyRepository->save($company);

        // Act
        $crawler = $client->request(Request::METHOD_GET, self::CONFIGURATION_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Configuration');

        $list = $crawler->filter('div.row > aside.col-md-3 > nav#menu > ul');

        self::assertCount(6, $list->children('li > a.w100'));
        self::assertCount(1, $list->children('li > a.disable-link'));
    }

    public function testConfigurePageWithApplicationConfigured(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);

        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(DoctrineUnitRepository::class);

        /** @var DoctrineTaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(DoctrineTaxRepository::class);

        $company = (new CompanyDataBuilder())->create('TestCompany')->build();
        $companyRepository->save($company);
        $unit = (new UnitDataBuilder())->create('Kilogramme', 'KG')->build();
        $unitRepository->save($unit);
        $tax = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $taxRepository->save($tax);

        // Act
        $crawler = $client->request(Request::METHOD_GET, self::CONFIGURATION_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Configuration');

        $list = $crawler->filter('div.row > aside.col-md-3 > nav#menu > ul');

        self::assertCount(6, $list->children('li > a.w100'));
        self::assertCount(2, $list->children('li > a.disable-link'));
    }

    public function testConfigurePageWithFamilyLog(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);

        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(DoctrineUnitRepository::class);

        /** @var DoctrineTaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(DoctrineTaxRepository::class);

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);

        $company = (new CompanyDataBuilder())->create('TestCompany')->build();
        $companyRepository->save($company);
        $unit = (new UnitDataBuilder())->create('Kilogramme', 'KG')->build();
        $unitRepository->save($unit);
        $tax = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $taxRepository->save($tax);
        $familyLog = (new FamilyLogDataBuilder())->create('Frais')->build();
        $familyLogRepository->save($familyLog);

        // Act
        $crawler = $client->request(Request::METHOD_GET, self::CONFIGURATION_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Configuration');

        $list = $crawler->filter('div.row > aside.col-md-3 > nav#menu > ul');

        self::assertCount(6, $list->children('li > a.w100'));
        self::assertCount(3, $list->children('li > a.disable-link'));
    }

    public function testConfigurePageWithZoneStorage(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);

        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(DoctrineUnitRepository::class);

        /** @var DoctrineTaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(DoctrineTaxRepository::class);

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);

        /** @var DoctrineZoneStorageRepository $zoneStorageRepository */
        $zoneStorageRepository = self::getContainer()->get(DoctrineZoneStorageRepository::class);

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
        $crawler = $client->request(Request::METHOD_GET, self::CONFIGURATION_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Configuration');

        $list = $crawler->filter('div.row > aside.col-md-3 > nav#menu > ul');

        self::assertCount(6, $list->children('li > a.w100'));
        self::assertCount(4, $list->children('li > a.disable-link'));
    }

    public function testConfigurePageWithSupplier(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);

        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(DoctrineUnitRepository::class);

        /** @var DoctrineTaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(DoctrineTaxRepository::class);

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);

        /** @var DoctrineZoneStorageRepository $zoneStorageRepository */
        $zoneStorageRepository = self::getContainer()->get(DoctrineZoneStorageRepository::class);

        /** @var DoctrineSupplierRepository $supplierRepository */
        $supplierRepository = self::getContainer()->get(DoctrineSupplierRepository::class);

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
        $crawler = $client->request(Request::METHOD_GET, self::CONFIGURATION_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Configuration');

        $list = $crawler->filter('div.row > aside.col-md-3 > nav#menu > ul');

        self::assertCount(6, $list->children('li > a.w100'));
        self::assertCount(5, $list->children('li > a.disable-link'));
    }
}
