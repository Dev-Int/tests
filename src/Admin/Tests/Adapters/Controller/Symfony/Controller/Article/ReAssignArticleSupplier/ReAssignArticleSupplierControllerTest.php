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

use Admin\Entities\Repository\ArticleRepository;
use Admin\Tests\Factory\ArticleFactory;
use Admin\Tests\Factory\FamilyLogFactory;
use Admin\Tests\Factory\SupplierFactory;
use Admin\Tests\Factory\TaxFactory;
use Admin\Tests\Factory\UnitFactory;
use Admin\Tests\Factory\ZoneStorageFactory;
use Faker\Factory;
use Shared\Tests\BaseFunctionalTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 */
final class ReAssignArticleSupplierControllerTest extends BaseFunctionalTestCase
{
    use Factories;

    private const REASSIGN_ARTICLE_SUPPLIER_URI = '/admin/articles/%s/reassign-supplier';

    public function testReAssignArticleSupplierWillSucceed(): void
    {
        // Arrange
        /** @var ArticleRepository $articleRepository */
        $articleRepository = self::getContainer()->get(ArticleRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        // Créer la configuration de base
        $tax = TaxFactory::createOne();
        $unit = UnitFactory::createOne();

        // Créer premier supplier avec FamilyLog "Frais"
        $familyLog1 = FamilyLogFactory::createOne(['label' => 'Frais']);
        $zoneStorage1 = ZoneStorageFactory::createOne(['label' => 'Frais', 'familyLog' => $familyLog1]);
        $supplier1 = SupplierFactory::createOne(['name' => 'Supplier 1', 'familyLog' => $familyLog1]);

        // Créer l'article avec le premier supplier
        $article = ArticleFactory::createOne([
            'name' => 'Jambon Trad 6kg',
            'supplier' => $supplier1,
            'tax' => $tax,
            'familyLog' => $familyLog1,
            'zoneStorages' => [$zoneStorage1],
            'packaging' => [[$unit->_real()->toDomain(), 1.0], null, null],
        ])->_real()->toDomain();

        // Créer 2ème supplier avec FamilyLog "Surgelé"
        $familyLog2 = FamilyLogFactory::createOne(['label' => 'Surgelé']);
        $zoneStorage2 = ZoneStorageFactory::createOne(['label' => 'Réserve négative', 'familyLog' => $familyLog2]);
        $supplier2 = SupplierFactory::createOne(['name' => 'Supplier Surgelé', 'familyLog' => $familyLog2]);

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
            'reAssignArticleSupplier[supplier]' => $supplier2->_real()->uuid(),
            'reAssignArticleSupplier[familyLog]' => $familyLog2->_real()->uuid(),
            'reAssignArticleSupplier[zoneStorages]' => [$zoneStorage2->_real()->uuid()],
            'reAssignArticleSupplier[uuid]' => $article->uuid()->toString(),
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/articles');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertEquals($translator->trans('admin.article.reassignSupplier.success'), $flash);

        $articleUpdated = $articleRepository->getByUuid($article->uuid());

        self::assertSame('Supplier Surgelé', $articleUpdated->supplier()->name()->toString());
        $zoneStorages = $articleUpdated->zoneStorages()->toArray();
        $firstZoneStorage = $zoneStorages[0];
        self::assertSame('Réserve négative', $firstZoneStorage->label()->toString());
        self::assertSame('Surgelé', $articleUpdated->familyLog()->label()->toString());
    }

    public function testReAssignArticleSupplierFailWithBadFamilyLogExceptionFromZoneStorage(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        // Créer la configuration de base
        $tax = TaxFactory::createOne();
        $unit = UnitFactory::createOne();

        // Créer l'article avec la famille Frais
        $familyLogFrais = FamilyLogFactory::createOne(['label' => 'Frais']);
        $zoneStorageFrais = ZoneStorageFactory::createOne(['label' => 'Produits frais', 'familyLog' => $familyLogFrais]);
        $supplierFrais = SupplierFactory::createOne(['name' => 'Supplier Frais', 'familyLog' => $familyLogFrais]);

        $articleProxy = ArticleFactory::createOne([
            'name' => 'Jambon Trad 6kg',
            'supplier' => $supplierFrais,
            'tax' => $tax,
            'familyLog' => $familyLogFrais,
            'zoneStorages' => [$zoneStorageFrais],
            'packaging' => [[$unit->_real()->toDomain(), 1.0], null, null],
        ]);
        $article = $articleProxy->_real()->toDomain();

        // Créer un 2ème supplier avec la famille Surgelé
        $familyLogSurgele = FamilyLogFactory::createOne(['label' => 'Surgelé']);
        $zoneStorageSurgele = ZoneStorageFactory::createOne(['label' => 'Réserve négative', 'familyLog' => $familyLogSurgele]);
        $supplierSurgele = SupplierFactory::createOne(['name' => 'Supplier Surgelé', 'familyLog' => $familyLogSurgele]);

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
            'reAssignArticleSupplier[supplier]' => $supplierSurgele->_real()->uuid(),
            'reAssignArticleSupplier[familyLog]' => $familyLogSurgele->_real()->uuid(),
            'reAssignArticleSupplier[zoneStorages]' => [
                $zoneStorageFrais->_real()->uuid(),
                $zoneStorageSurgele->_real()->uuid(),
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
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        // Créer la configuration de base
        $tax = TaxFactory::createOne();
        $unit = UnitFactory::createOne();

        // Créer l'article avec la famille Frais
        $familyLogFrais = FamilyLogFactory::createOne(['label' => 'Frais']);
        $zoneStorageFrais = ZoneStorageFactory::createOne(['label' => 'Produits frais', 'familyLog' => $familyLogFrais]);
        $supplierFrais = SupplierFactory::createOne(['name' => 'Supplier Frais', 'familyLog' => $familyLogFrais]);

        $articleProxy = ArticleFactory::createOne([
            'name' => 'Jambon Trad 6kg',
            'supplier' => $supplierFrais,
            'tax' => $tax,
            'familyLog' => $familyLogFrais,
            'zoneStorages' => [$zoneStorageFrais],
            'packaging' => [[$unit->_real()->toDomain(), 1.0], null, null],
        ]);
        $article = $articleProxy->_real()->toDomain();

        // Créer un 2ème supplier avec la famille Surgelé
        $familyLogSurgele = FamilyLogFactory::createOne(['label' => 'Surgelé']);
        $zoneStorageSurgele = ZoneStorageFactory::createOne(['label' => 'Réserve négative', 'familyLog' => $familyLogSurgele]);
        $supplierSurgele = SupplierFactory::createOne(['name' => 'Supplier Surgelé', 'familyLog' => $familyLogSurgele]);

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
            'reAssignArticleSupplier[supplier]' => $supplierSurgele->_real()->uuid(),
            'reAssignArticleSupplier[familyLog]' => $familyLogFrais->_real()->uuid(),
            'reAssignArticleSupplier[zoneStorages]' => [
                $zoneStorageSurgele->_real()->uuid(),
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

        // Créer un article pour avoir des données en base (test réaliste)
        $tax = TaxFactory::createOne();
        $familyLog = FamilyLogFactory::createOne();
        $zoneStorage = ZoneStorageFactory::createOne(['familyLog' => $familyLog]);
        $supplier = SupplierFactory::createOne(['familyLog' => $familyLog]);
        $unit = UnitFactory::createOne();

        ArticleFactory::createOne([
            'name' => 'Jambon Trad 6kg',
            'supplier' => $supplier,
            'tax' => $tax,
            'familyLog' => $familyLog,
            'zoneStorages' => [$zoneStorage],
            'packaging' => [[$unit->_real()->toDomain(), 1.0], null, null],
        ]);

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
