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
final class HomeControllerTest extends BaseFunctionalTestCase
{
    private const HOME_URI = '/admin/';

    public function testHomePageWillSucceed(): void
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

        $company = (new CompanyDataBuilder())->create('TestCompany')->build();
        $companyRepository->save($company);

        $unit = (new UnitDataBuilder())->create('Kilogramme', 'kg')->build();
        $unitRepository->save($unit);

        $tax = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $taxRepository->save($tax);

        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')->build();
        $familyLogRepository->save($familyLog);

        $zoneStorage = (new ZoneStorageDataBuilder())
            ->create('Réserve négative', $familyLog)
            ->build()
        ;
        $zoneStorageRepository->save($zoneStorage);

        $supplier = (new SupplierDataBuilder())->create('supplier 1', $familyLog)->build();
        $supplierRepository->save($supplier);

        $article = (new ArticleDataBuilder())
            ->create(
                'article 1',
                $supplier,
                $tax,
                [$zoneStorage],
                $familyLog,
                [[$unit, 1.0], null, null]
            )
            ->build()
        ;
        $articleRepository->save($article);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::HOME_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.titlePage'));
        $brand = $crawler->filter('body > header > nav')->children('ul')->first();
        self::assertSame('Application', $brand->text());
    }
}
