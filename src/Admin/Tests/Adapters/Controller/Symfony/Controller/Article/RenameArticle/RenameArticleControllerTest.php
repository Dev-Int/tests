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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\Article\RenameArticle;

use Admin\Entities\Exception\Article\ArticleAlreadyExistsException;
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
final class RenameArticleControllerTest extends BaseFunctionalTestCase
{
    private const RENAME_ARTICLE_URI = '/admin/articles/%s/rename';

    public function testRenameArticleWillSucceed(): void
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

        $familyLog = (new FamilyLogDataBuilder())->create('Frais')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLogRepository->save($familyLog);

        $zoneStorage = (new ZoneStorageDataBuilder())->create('Réserve froide', $familyLog)->build();
        $zoneStorageRepository->save($zoneStorage);

        $supplier = (new SupplierDataBuilder())->create('Supplier 1', $familyLog)->build();
        $supplierRepository->save($supplier);

        $article = (new ArticleDataBuilder())->create(
            'Jambon Trad 6kg',
            $supplier,
            $tax,
            [$zoneStorage],
            $familyLog,
            [[$colis, 1.0], null, null]
        )->build();
        $articleRepository->save($article);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::RENAME_ARTICLE_URI, $article->uuid()->toString())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans('admin.article.rename.titlePage', ['%articleName%' => $article->name()->toString()])
        );

        $form = $crawler->selectButton($translator->trans('admin.article.rename.button'))->form([
            'renameArticle[name]' => 'Jambon 6kg',
            'renameArticle[uuid]' => $article->uuid()->toString(),
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/articles');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertEquals($translator->trans('admin.article.rename.success'), $flash);

        $articleUpdated = $articleRepository->findByUuid($article->uuid()->toString());
        self::assertSame('Jambon 6kg', $articleUpdated->name()->toString());
    }

    public function testRenameArticleFailWithAlreadyExistsException(): void
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

        $familyLog = (new FamilyLogDataBuilder())->create('Frais')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLogRepository->save($familyLog);

        $zoneStorage = (new ZoneStorageDataBuilder())->create('Réserve froide', $familyLog)->build();
        $zoneStorageRepository->save($zoneStorage);

        $supplier = (new SupplierDataBuilder())->create('Supplier 1', $familyLog)->build();
        $supplierRepository->save($supplier);

        $article1 = (new ArticleDataBuilder())->create(
            'Jambon Trad 6kg',
            $supplier,
            $tax,
            [$zoneStorage],
            $familyLog,
            [[$colis, 1.0], null, null]
        )->build();
        $articleRepository->save($article1);
        $article2 = (new ArticleDataBuilder())->create(
            'Jambon 6kg',
            $supplier,
            $tax,
            [$zoneStorage],
            $familyLog,
            [[$colis, 1.0], null, null]
        )
            ->withUuid($faker->uuid())
            ->build()
        ;
        $articleRepository->save($article2);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::RENAME_ARTICLE_URI, $article1->uuid()->toString())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans('admin.article.rename.titlePage', ['%articleName%' => $article1->name()->toString()])
        );

        $form = $crawler->selectButton($translator->trans('admin.article.rename.button'))->form([
            'renameArticle[name]' => 'Jambon 6kg',
            'renameArticle[uuid]' => $article1->uuid()->toString(),
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/articles');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertEquals(ArticleAlreadyExistsException::MESSAGE, $flash);
    }

    public function testRenameArticleFailWithArticleNotFound(): void
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

        $familyLog = (new FamilyLogDataBuilder())->create('Frais')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLogRepository->save($familyLog);

        $zoneStorage = (new ZoneStorageDataBuilder())->create('Réserve froide', $familyLog)->build();
        $zoneStorageRepository->save($zoneStorage);

        $supplier = (new SupplierDataBuilder())->create('Supplier 1', $familyLog)->build();
        $supplierRepository->save($supplier);

        $article = (new ArticleDataBuilder())->create(
            'Jambon Trad 6kg',
            $supplier,
            $tax,
            [$zoneStorage],
            $familyLog,
            [[$colis, 1.0], null, null]
        )->build();
        $articleRepository->save($article);

        // Act
        $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::RENAME_ARTICLE_URI, $faker->uuid())
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $response = $this->client->getCrawler();

        $title = $response->filter('h1')->text();

        self::assertEquals('Page non trouvée', $title);
    }
}
