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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\Article\ChangeFinancialInformation;

use Admin\Adapters\Gateway\ORM\Entity\Article\Article;
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

final class ChangeFinancialInformationControllerTest extends WebTestCase
{
    public const CHANGE_ARTICLE_FINANCIAL_INFORMATION_URI = '/admin/articles/%s/change-financial-information';

    public function testChangeFinancialInformationWillSucceed(): void
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

        $tax20 = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $tax55 = (new TaxDataBuilder())->create('TVA taux réduit', 5.5)
            ->withUuid($faker->uuid())
            ->build()
        ;
        $taxRepository->save($tax20);
        $taxRepository->save($tax55);

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
                $tax20,
                [$storageSurgele],
                $frais,
                [[$colis, 1.0], null, null]
            )
            ->build()
        ;
        $articleRepository->save($article);

        // Act
        $crawler = $client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_ARTICLE_FINANCIAL_INFORMATION_URI, $article->uuid()->toString())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Change financial information to "Jambon Trad 6kg"');

        $form = $crawler->selectButton('Update')->form([
            'changeArticleFinancialInformation[amount]' => 7.25,
            'changeArticleFinancialInformation[tax]' => $tax55->uuid()->toString(),
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/articles');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertEquals('Article updated', $flash);

        $articleUpdated = $articleRepository->findOneBy(['slug' => 'jambon-trad-6kg']);
        self::assertInstanceOf(Article::class, $articleUpdated);
        self::assertSame(725, $articleUpdated->amount());
        self::assertSame(0.055, $articleUpdated->tax()->rate());
    }

    public function testChangeFinancialInformationFailWithArticleNotFoundException(): void
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

        $tax20 = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $tax55 = (new TaxDataBuilder())->create('TVA taux réduit', 5.5)
            ->withUuid($faker->uuid())
            ->build()
        ;
        $taxRepository->save($tax20);
        $taxRepository->save($tax55);

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
                $tax20,
                [$storageSurgele],
                $frais,
                [[$colis, 1.0], null, null]
            )
            ->build()
        ;
        $articleRepository->save($article);

        // Act
        $client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_ARTICLE_FINANCIAL_INFORMATION_URI, $faker->uuid())
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $response = $client->getCrawler();
        $title = $response->filter('h1')->text();

        self::assertEquals('Page non trouvée', $title);
    }
}
