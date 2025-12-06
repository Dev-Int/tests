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

use Admin\Tests\DataBuilder\ArticleDataBuilder;
use Admin\Tests\DataBuilder\CompanyDataBuilder;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use Admin\Tests\DataBuilder\ZoneStorageDataBuilder;
use Admin\UseCases\Gateway\ArticleRepository;
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
final class ApplicationConfigureControllerTest extends BaseFunctionalTestCase
{
    private const APPLICATION_CONFIGURE_URI = '/admin/configure/application';

    public function testApplicationConfigurePageWillSucceed(): void
    {
        // Arrange
        /** @var CompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(CompanyRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $company = (new CompanyDataBuilder())->create('TestCompany')->build();
        $companyRepository->save($company);

        // Act
        $this->client->request(Request::METHOD_GET, self::APPLICATION_CONFIGURE_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.configuration.application.titlePage'));
    }

    public function testApplicationConfigured(): void
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

        /** @var ArticleRepository $articleRepository */
        $articleRepository = self::getContainer()->get(ArticleRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $company = (new CompanyDataBuilder())->create('Test company')->build();
        $companyRepository->save($company);

        $colis = (new UnitDataBuilder())->create('Colis', 'kg')->build();
        $unitRepository->save($colis);

        $tax = (new TaxDataBuilder())->create('TVA taux réduit', 5.5)->build();
        $taxRepository->save($tax);

        $familyLog = (new FamilyLogDataBuilder())->create('Frais')->build();
        $familyLogRepository->save($familyLog);

        $zoneStorage = (new ZoneStorageDataBuilder())->create('Réserve froide', $familyLog)->build();
        $zoneStorageRepository->save($zoneStorage);

        $supplier = (new SupplierDataBuilder())->create('Supplier 1', $familyLog)->build();
        $supplierRepository->save($supplier);

        $article = (new ArticleDataBuilder())->create(
            'Article 1',
            $supplier,
            $tax,
            [$zoneStorage],
            $familyLog,
            [[$colis, 1.0], null, null]
        )->build();
        $articleRepository->save($article);

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
