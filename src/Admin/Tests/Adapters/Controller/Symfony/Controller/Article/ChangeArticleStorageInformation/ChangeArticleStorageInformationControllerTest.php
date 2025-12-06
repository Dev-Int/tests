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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\Article\ChangeArticleStorageInformation;

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
class ChangeArticleStorageInformationControllerTest extends BaseFunctionalTestCase
{
    public const CHANGE_ARTICLE_STORAGE_INFORMATION_URI = '/admin/articles/%s/change-article-storage-information';

    public function testChangeArticleStorageInformationWillSucceed(): void
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
        $piece = (new UnitDataBuilder())
            ->create('Pièce', 'kg')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $kilogramme = (new UnitDataBuilder())
            ->create('Kilogramme', 'kg')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $unitRepository->save($colis);
        $unitRepository->save($piece);
        $unitRepository->save($kilogramme);

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
        $zoneStorageRepository->save($storageSurgele);

        $supplierSurgele = (new SupplierDataBuilder())->create('Supplier Surgelé', $frais)->build();
        $supplierRepository->save($supplierSurgele);

        $article = (new ArticleDataBuilder())
            ->create(
                'Jambon Trad 6kg',
                $supplierSurgele,
                $tax,
                [$storageSurgele],
                $frais,
                [[$colis, 1.0], null, null]
            )
            ->build()
        ;
        $articleRepository->save($article);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_ARTICLE_STORAGE_INFORMATION_URI, $article->uuid()->toString())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans(
                'admin.article.changeStorageInformation.titlePage',
                ['%articleName%' => $article->name()->toString()]
            )
        );

        $form = $crawler->selectButton($translator->trans('admin.article.changeStorageInformation.button'))->form([
            'changeArticleStorageInformation[packaging][parcel][unit]' => $colis->uuid()->toString(),
            'changeArticleStorageInformation[packaging][parcel][quantity]' => 1,
            'changeArticleStorageInformation[packaging][subPackage][unit]' => $piece->uuid()->toString(),
            'changeArticleStorageInformation[packaging][subPackage][quantity]' => 2,
            'changeArticleStorageInformation[packaging][consumeUnit][unit]' => $kilogramme->uuid()->toString(),
            'changeArticleStorageInformation[packaging][consumeUnit][quantity]' => 6.800,
            'changeArticleStorageInformation[minStock]' => 6.8,
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/articles');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        static::assertEquals($translator->trans('admin.article.changeStorageInformation.success'), $flash);

        $articleUpdated = $articleRepository->findByUuid($article->uuid()->toString());
        static::assertEquals([$colis, 1.0], $articleUpdated->packaging()->parcel());
        static::assertEquals([$piece, 2.0], $articleUpdated->packaging()->subPackage());
        static::assertEquals([$kilogramme, 6.800], $articleUpdated->packaging()->consumerUnit());
        static::assertEquals(6.8, $articleUpdated->minStock());
    }

    public function testChangeArticleStorageInformationFailWithArticleNotfoundException(): void
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
        $piece = (new UnitDataBuilder())
            ->create('Pièce', 'kg')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $kilogramme = (new UnitDataBuilder())
            ->create('Kilogramme', 'kg')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $unitRepository->save($colis);
        $unitRepository->save($piece);
        $unitRepository->save($kilogramme);

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
        $zoneStorageRepository->save($storageSurgele);

        $supplierSurgele = (new SupplierDataBuilder())->create('Supplier Surgelé', $frais)->build();
        $supplierRepository->save($supplierSurgele);

        $article = (new ArticleDataBuilder())
            ->create(
                'Jambon Trad 6kg',
                $supplierSurgele,
                $tax,
                [$storageSurgele],
                $frais,
                [[$colis, 1.0], null, null]
            )
            ->build()
        ;
        $articleRepository->save($article);

        // Act
        $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_ARTICLE_STORAGE_INFORMATION_URI, $faker->uuid())
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $response = $this->client->getCrawler();

        $title = $response->filter('h1')->text();

        static::assertEquals('Page non trouvée', $title);
    }
}
