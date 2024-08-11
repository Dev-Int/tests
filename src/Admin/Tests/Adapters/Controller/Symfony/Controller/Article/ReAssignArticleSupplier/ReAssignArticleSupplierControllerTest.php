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

use Admin\Adapters\Gateway\ORM\Entity\Article\Article;
use Admin\Adapters\Gateway\ORM\Entity\ZoneStorage;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineArticleRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineSupplierRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineTaxRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineUnitRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineZoneStorageRepository;
use Admin\Tests\DataBuilder\ArticleDataBuilder;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use Admin\Tests\DataBuilder\ZoneStorageDataBuilder;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group functionalTest
 */
final class ReAssignArticleSupplierControllerTest extends WebTestCase
{
    private const REASSIGN_ARTICLE_SUPPLIER_URI = '/admin/articles/%s/reassign-supplier';

    public function testReAssignArticleSupplierWillSucceed(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $client = self::createClient();

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

        /** @var DoctrineArticleRepository $articleRepository */
        $articleRepository = self::getContainer()->get(DoctrineArticleRepository::class);

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
        $crawler = $client->request(
            Request::METHOD_GET,
            sprintf(self::REASSIGN_ARTICLE_SUPPLIER_URI, $article->uuid()->toString())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Re-assign supplier to "Jambon Trad 6kg"');

        $form = $crawler->selectButton('Re-assign supplier')->form([
            'reAssignArticleSupplier[supplier]' => $supplierSurgele->uuid()->toString(),
            'reAssignArticleSupplier[familyLog]' => $surgele->uuid()->toString(),
            'reAssignArticleSupplier[zoneStorages]' => [$storageSurgele->uuid()->toString()],
            'reAssignArticleSupplier[uuid]' => $article->uuid()->toString(),
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/articles');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertEquals('Article updated', $flash);

        $articleUpdated = $articleRepository->find($article->uuid()->toString());
        self::assertInstanceOf(Article::class, $articleUpdated);
        self::assertSame('Supplier Surgelé', $articleUpdated->supplier()->name());
        $zoneStorages = $articleUpdated->zoneStorages();
        $firstZoneStorage = $zoneStorages[0];
        self::assertInstanceOf(ZoneStorage::class, $firstZoneStorage);
        self::assertSame('Réserve négative', $firstZoneStorage->label());
        self::assertSame('Surgelé', $articleUpdated->familyLog()->label());
    }

    public function testReAssignArticleSupplierFailWithBadFamilyLogExceptionFromZoneStorage(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $client = self::createClient();

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

        /** @var DoctrineArticleRepository $articleRepository */
        $articleRepository = self::getContainer()->get(DoctrineArticleRepository::class);

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
        $crawler = $client->request(
            Request::METHOD_GET,
            sprintf(self::REASSIGN_ARTICLE_SUPPLIER_URI, $article->uuid()->toString())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Re-assign supplier to "Jambon Trad 6kg"');

        $form = $crawler->selectButton('Re-assign supplier')->form([
            'reAssignArticleSupplier[supplier]' => $supplierSurgele->uuid()->toString(),
            'reAssignArticleSupplier[familyLog]' => $surgele->uuid()->toString(),
            'reAssignArticleSupplier[zoneStorages]' => [
                $storageFrais->uuid()->toString(),
                $storageSurgele->uuid()->toString(),
            ],
            'reAssignArticleSupplier[uuid]' => $article->uuid()->toString(),
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $crawler = $client->getCrawler();

        $supplierField = $crawler->filter('form')->children('div')->first();

        self::assertSame('Supplier', $supplierField->children('label')->text());
        $siblings = $supplierField->siblings()->first();
        $zoneStoragesField = $siblings->children('div')->first();
        self::assertSame('Zone de stockage', $zoneStoragesField->children('label')->text());
        self::assertSame(
            'The zoneStorages logistic family "Frais" is not compatible with the supplier logistic family: "Surgelé"',
            $zoneStoragesField->children('ul > li')->text()
        );
    }

    public function testReAssignArticleSupplierFailWithBadFamilyLogExceptionFromFamilyLog(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $client = self::createClient();

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

        /** @var DoctrineArticleRepository $articleRepository */
        $articleRepository = self::getContainer()->get(DoctrineArticleRepository::class);

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
        $crawler = $client->request(
            Request::METHOD_GET,
            sprintf(self::REASSIGN_ARTICLE_SUPPLIER_URI, $article->uuid()->toString())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Re-assign supplier to "Jambon Trad 6kg"');

        $form = $crawler->selectButton('Re-assign supplier')->form([
            'reAssignArticleSupplier[supplier]' => $supplierSurgele->uuid()->toString(),
            'reAssignArticleSupplier[familyLog]' => $frais->uuid()->toString(),
            'reAssignArticleSupplier[zoneStorages]' => [
                $storageSurgele->uuid()->toString(),
            ],
            'reAssignArticleSupplier[uuid]' => $article->uuid()->toString(),
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $crawler = $client->getCrawler();

        $supplierField = $crawler->filter('form')->children('div')->first();

        self::assertSame('Supplier', $supplierField->children('label')->text());
        $siblings = $supplierField->siblings()->first();
        $zoneStoragesField = $siblings->children('div')->first();
        self::assertSame('Zone de stockage', $zoneStoragesField->children('label')->text());
        $familyLogField = $zoneStoragesField->siblings();
        self::assertSame('Famille logistique', $familyLogField->children('label')->text());
        self::assertSame(
            'The familyLog logistic family "Frais" is not compatible with the supplier logistic family: "Surgelé"',
            $familyLogField->children('ul > li')->text()
        );
    }

    public function testReAssignArticleSupplierFailWithArticleNotFound(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $client = self::createClient();

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

        /** @var DoctrineArticleRepository $articleRepository */
        $articleRepository = self::getContainer()->get(DoctrineArticleRepository::class);

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
        $client->request(
            Request::METHOD_GET,
            sprintf(self::REASSIGN_ARTICLE_SUPPLIER_URI, $faker->uuid())
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $response = $client->getCrawler();

        $title = $response->filter('h1')->text();

        self::assertEquals('Page non trouvée', $title);
    }
}
