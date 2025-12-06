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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\Article\ReAssignArticleSupplier;

use Admin\Tests\DataBuilder\ArticleDataBuilder;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use Admin\Tests\DataBuilder\ZoneStorageDataBuilder;
use Admin\UseCases\Gateway\ArticleRepository;
use Admin\UseCases\Gateway\FamilyLogRepository;
use Admin\UseCases\Gateway\SupplierRepository;
use Admin\UseCases\Gateway\TaxRepository;
use Admin\UseCases\Gateway\UnitRepository;
use Admin\UseCases\Gateway\ZoneStorageRepository;
use App\Shared\Tests\BaseFunctionalTestCase;
use Faker\Factory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group functionalTest
 */
final class ReAssignArticleSupplierControllerTest extends BaseFunctionalTestCase
{
    private const REASSIGN_ARTICLE_SUPPLIER_URI = '/admin/articles/%s/reassign-supplier';

    public function testReAssignArticleSupplierWillSucceed(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

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

        $colis = (new UnitDataBuilder())->create('Colis', 'kg')->build();
        $unitRepository->save($colis);

        $tax = (new TaxDataBuilder())->create('TVA taux réduit', 5.5)->build();
        $taxRepository->save($tax);

        $surgele = (new FamilyLogDataBuilder())->create('Surgelé')->build();
        $frais = (new FamilyLogDataBuilder())
            ->create('Frais')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLogRepository->save($surgele);
        $familyLogRepository->save($frais);

        $storageSurgele = (new ZoneStorageDataBuilder())->create('Réserve négative', $surgele)->build();
        $storageFrais = (new ZoneStorageDataBuilder())
            ->create('Réserve positive', $frais)
            ->withUuid($faker->uuid())
            ->build()
        ;
        $zoneStorageRepository->save($storageFrais);
        $zoneStorageRepository->save($storageSurgele);

        $supplierSurgele = (new SupplierDataBuilder())->create('Supplier Surgelé', $surgele)->build();
        $supplierFrais = (new SupplierDataBuilder())
            ->create('Supplier Frais', $frais)
            ->withUuid($faker->uuid())
            ->build()
        ;
        $supplierRepository->save($supplierSurgele);
        $supplierRepository->save($supplierFrais);

        $article = (new ArticleDataBuilder())
            ->create(
                'Jambon Trad 6kg',
                $supplierFrais,
                $tax,
                [$storageFrais],
                $frais,
                [[$colis, 1.0], null, null]
            )
            ->build()
        ;
        $articleRepository->save($article);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::REASSIGN_ARTICLE_SUPPLIER_URI, $article->uuid()->toString())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans(
                'admin.article.reassignSupplier.titlePage',
                ['%articleName%' => $article->name()->toString()]
            )
        );

        $form = $crawler->selectButton($translator->trans('admin.article.reassignSupplier.button'))->form([
            'reAssignArticleSupplier[supplier]' => $supplierSurgele->uuid()->toString(),
            'reAssignArticleSupplier[familyLog]' => $surgele->uuid()->toString(),
            'reAssignArticleSupplier[zoneStorages]' => [$storageSurgele->uuid()->toString()],
            'reAssignArticleSupplier[uuid]' => $article->uuid()->toString(),
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/articles');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertEquals($translator->trans('admin.article.reassignSupplier.success'), $flash);

        $articleUpdated = $articleRepository->findByUuid($article->uuid()->toString());

        self::assertSame('Supplier Surgelé', $articleUpdated->supplier()->name()->toString());
        $zoneStorages = $articleUpdated->zoneStorages()->toArray();
        $firstZoneStorage = $zoneStorages[0];
        self::assertSame('Réserve négative', $firstZoneStorage->label()->toString());
        self::assertSame('Surgelé', $articleUpdated->familyLog()->label()->toString());
    }

    public function testReAssignArticleSupplierFailWithBadFamilyLogExceptionFromZoneStorage(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

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

        $colis = (new UnitDataBuilder())->create('Colis', 'kg')->build();
        $unitRepository->save($colis);

        $tax = (new TaxDataBuilder())->create('TVA taux réduit', 5.5)->build();
        $taxRepository->save($tax);

        $surgele = (new FamilyLogDataBuilder())->create('Surgelé')->build();
        $frais = (new FamilyLogDataBuilder())->create('Frais')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLogRepository->save($surgele);
        $familyLogRepository->save($frais);

        $storageSurgele = (new ZoneStorageDataBuilder())->create('Réserve négative', $surgele)->build();
        $storageFrais = (new ZoneStorageDataBuilder())->create('Réserve positive', $frais)
            ->withUuid($faker->uuid())
            ->build()
        ;
        $zoneStorageRepository->save($storageFrais);
        $zoneStorageRepository->save($storageSurgele);

        $supplierSurgele = (new SupplierDataBuilder())->create('Supplier Surgelé', $surgele)->build();
        $supplierFrais = (new SupplierDataBuilder())->create('Supplier Frais', $frais)
            ->withUuid($faker->uuid())
            ->build()
        ;
        $supplierRepository->save($supplierSurgele);
        $supplierRepository->save($supplierFrais);

        $article = (new ArticleDataBuilder())->create(
            'Jambon Trad 6kg',
            $supplierFrais,
            $tax,
            [$storageFrais],
            $frais,
            [[$colis, 1.0], null, null]
        )->build();
        $articleRepository->save($article);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::REASSIGN_ARTICLE_SUPPLIER_URI, $article->uuid()->toString())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans(
                'admin.article.reassignSupplier.titlePage',
                ['%articleName%' => $article->name()->toString()]
            )
        );

        $form = $crawler->selectButton($translator->trans('admin.article.reassignSupplier.button'))->form([
            'reAssignArticleSupplier[supplier]' => $supplierSurgele->uuid()->toString(),
            'reAssignArticleSupplier[familyLog]' => $surgele->uuid()->toString(),
            'reAssignArticleSupplier[zoneStorages]' => [
                $storageFrais->uuid()->toString(),
                $storageSurgele->uuid()->toString(),
            ],
            'reAssignArticleSupplier[uuid]' => $article->uuid()->toString(),
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $crawler = $this->client->getCrawler();

        $supplierField = $crawler->filter('form')->children('div')->first();

        self::assertSame(
            $translator->trans('admin.article.form.supplier.label'),
            $supplierField->children('label')->text()
        );
        $siblings = $supplierField->siblings()->first();
        $zoneStoragesField = $siblings->children('div')->first();
        self::assertSame(
            $translator->trans('admin.article.form.zoneStorages.label'),
            $zoneStoragesField->children('label')->text()
        );
        self::assertSame(
            'Le champ Zones de stockage "Frais" n\'est pas compatible avec la famille logistique du fournisseur: "Surgelé"',
            $zoneStoragesField->children('ul > li')->text()
        );
    }

    public function testReAssignArticleSupplierFailWithBadFamilyLogExceptionFromFamilyLog(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

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

        $colis = (new UnitDataBuilder())->create('Colis', 'kg')->build();
        $unitRepository->save($colis);

        $tax = (new TaxDataBuilder())->create('TVA taux réduit', 5.5)->build();
        $taxRepository->save($tax);

        $surgele = (new FamilyLogDataBuilder())->create('Surgelé')->build();
        $frais = (new FamilyLogDataBuilder())->create('Frais')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLogRepository->save($surgele);
        $familyLogRepository->save($frais);

        $storageSurgele = (new ZoneStorageDataBuilder())->create('Réserve négative', $surgele)->build();
        $storageFrais = (new ZoneStorageDataBuilder())->create('Réserve positive', $frais)
            ->withUuid($faker->uuid())
            ->build()
        ;
        $zoneStorageRepository->save($storageFrais);
        $zoneStorageRepository->save($storageSurgele);

        $supplierSurgele = (new SupplierDataBuilder())->create('Supplier Surgelé', $surgele)->build();
        $supplierFrais = (new SupplierDataBuilder())->create('Supplier Frais', $frais)
            ->withUuid($faker->uuid())
            ->build()
        ;
        $supplierRepository->save($supplierSurgele);
        $supplierRepository->save($supplierFrais);

        $article = (new ArticleDataBuilder())->create(
            'Jambon Trad 6kg',
            $supplierFrais,
            $tax,
            [$storageFrais],
            $frais,
            [[$colis, 1.0], null, null]
        )->build();
        $articleRepository->save($article);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::REASSIGN_ARTICLE_SUPPLIER_URI, $article->uuid()->toString())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans(
                'admin.article.reassignSupplier.titlePage',
                ['%articleName%' => $article->name()->toString()]
            )
        );

        $form = $crawler->selectButton($translator->trans('admin.article.reassignSupplier.button'))->form([
            'reAssignArticleSupplier[supplier]' => $supplierSurgele->uuid()->toString(),
            'reAssignArticleSupplier[familyLog]' => $frais->uuid()->toString(),
            'reAssignArticleSupplier[zoneStorages]' => [
                $storageSurgele->uuid()->toString(),
            ],
            'reAssignArticleSupplier[uuid]' => $article->uuid()->toString(),
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $crawler = $this->client->getCrawler();

        $supplierField = $crawler->filter('form')->children('div')->first();

        self::assertSame(
            $translator->trans('admin.article.form.supplier.label'),
            $supplierField->children('label')->text()
        );
        $siblings = $supplierField->siblings()->first();
        $zoneStoragesField = $siblings->children('div')->first();
        self::assertSame(
            $translator->trans('admin.article.form.zoneStorages.label'),
            $zoneStoragesField->children('label')->text()
        );
        $familyLogField = $zoneStoragesField->siblings();
        self::assertSame(
            $translator->trans('admin.article.form.familyLog.label'),
            $familyLogField->children('label')->text()
        );
        self::assertSame(
            'Le champ Famille logistique "Frais" n\'est pas compatible avec la famille logistique du fournisseur: "Surgelé"',
            $familyLogField->children('ul > li')->text()
        );
    }

    public function testReAssignArticleSupplierFailWithArticleNotFound(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

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

        $colis = (new UnitDataBuilder())->create('Colis', 'kg')->build();
        $unitRepository->save($colis);

        $tax = (new TaxDataBuilder())->create('TVA taux réduit', 5.5)->build();
        $taxRepository->save($tax);

        $surgele = (new FamilyLogDataBuilder())->create('Surgelé')->build();
        $frais = (new FamilyLogDataBuilder())
            ->create('Frais')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLogRepository->save($surgele);
        $familyLogRepository->save($frais);

        $storageSurgele = (new ZoneStorageDataBuilder())->create('Réserve négative', $surgele)->build();
        $storageFrais = (new ZoneStorageDataBuilder())
            ->create('Réserve positive', $frais)
            ->withUuid($faker->uuid())
            ->build()
        ;
        $zoneStorageRepository->save($storageFrais);
        $zoneStorageRepository->save($storageSurgele);

        $supplierSurgele = (new SupplierDataBuilder())->create('Supplier Surgelé', $surgele)->build();
        $supplierFrais = (new SupplierDataBuilder())
            ->create('Supplier Frais', $frais)
            ->withUuid($faker->uuid())
            ->build()
        ;
        $supplierRepository->save($supplierSurgele);
        $supplierRepository->save($supplierFrais);

        $article = (new ArticleDataBuilder())
            ->create(
                'Jambon Trad 6kg',
                $supplierFrais,
                $tax,
                [$storageFrais],
                $frais,
                [[$colis, 1.0], null, null]
            )
            ->build()
        ;
        $articleRepository->save($article);

        // Act
        $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::REASSIGN_ARTICLE_SUPPLIER_URI, $faker->uuid())
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $response = $this->client->getCrawler();

        $title = $response->filter('h1')->text();

        self::assertEquals('Page non trouvée', $title);
    }
}
