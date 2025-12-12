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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\Article\CreateArticle;

use Admin\Entities\Exception\Article\ArticleAlreadyExists;
use Admin\Entities\Exception\Supplier\NoSupplierRegistered;
use Admin\Entities\Repository\ArticleRepository;
use Admin\Tests\Factory\ArticleFactory;
use Admin\Tests\Factory\CompanyFactory;
use Admin\Tests\Factory\FamilyLogFactory;
use Admin\Tests\Factory\SupplierFactory;
use Admin\Tests\Factory\TaxFactory;
use Admin\Tests\Factory\UnitFactory;
use Admin\Tests\Factory\ZoneStorageFactory;
use App\Shared\Tests\BaseFunctionalTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 */
final class CreateArticleControllerTest extends BaseFunctionalTestCase
{
    use Factories;

    private const CREATE_ARTICLE_URI = '/admin/articles/create';

    public function testCreateArticleWillSucceed(): void
    {
        // Arrange
        /** @var ArticleRepository $articleRepository */
        $articleRepository = self::getContainer()->get(ArticleRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        // Créer la configuration directement avec Foundry
        CompanyFactory::createOne(['name' => 'Test company']);
        $tax = TaxFactory::createOne(['name' => 'TVA taux réduit', 'rate' => 5.5]);

        // Créer les unités de packaging
        $colis = UnitFactory::createOne(['label' => 'Colis']);
        $piece = UnitFactory::createOne(['label' => 'Pièce']);
        $kilogramme = UnitFactory::createOne(['label' => 'Kilogramme']);

        // Créer la hiérarchie FamilyLog: Alimentaire > Frais > Viande
        $familyLog0 = FamilyLogFactory::createOne(['label' => 'Alimentaire']);
        $familyLog1 = FamilyLogFactory::createOne(['label' => 'Frais', 'parent' => $familyLog0->_real()]);
        $familyLog2 = FamilyLogFactory::createOne(['label' => 'Viande', 'parent' => $familyLog1->_real()]);

        // Créer ZoneStorage lié au parent (Frais)
        $zoneStorage = ZoneStorageFactory::createOne(['label' => 'Réserve froide', 'familyLog' => $familyLog1]);

        // Créer Supplier lié au grand-parent (Alimentaire)
        $supplier = SupplierFactory::createOne(['name' => 'Supplier 1', 'familyLog' => $familyLog0]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_ARTICLE_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.article.create.titlePage'));

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createArticle[name]' => 'Jambon Trad 6kg',
            'createArticle[supplier]' => $supplier->_real()->uuid(),
            'createArticle[packaging][parcel][unit]' => $colis->_real()->uuid(),
            'createArticle[packaging][parcel][quantity]' => 1,
            'createArticle[packaging][subPackage][unit]' => $piece->_real()->uuid(),
            'createArticle[packaging][subPackage][quantity]' => 2,
            'createArticle[packaging][consumeUnit][unit]' => $kilogramme->_real()->uuid(),
            'createArticle[packaging][consumeUnit][quantity]' => 6.800,
            'createArticle[unitPrice]' => 6.82,
            'createArticle[tax]' => $tax->_real()->uuid(),
            'createArticle[minStock]' => 8.8,
            'createArticle[zoneStorages]' => [$zoneStorage->_real()->uuid()],
            'createArticle[familyLog]' => $familyLog2->_real()->uuid(),
            'createArticle[quantity]' => 12.500,
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/articles');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertEquals($translator->trans('admin.article.create.success'), $flash);

        $articleCreated = $articleRepository->getBySlug('jambon-trad-6kg');
        self::assertSame('Jambon Trad 6kg', $articleCreated->name()->toString());
        self::assertSame('Supplier 1', $articleCreated->supplier()->name()->toString());
        self::assertSame('Alimentaire', $articleCreated->supplier()->familyLog()->label()->toString());
        self::assertEquals([$colis->_real()->toDomain(), 1.0], $articleCreated->packaging()->parcel());
        self::assertEquals([$piece->_real()->toDomain(), 2.0], $articleCreated->packaging()->subPackage());
        self::assertEquals([$kilogramme->_real()->toDomain(), 6.800], $articleCreated->packaging()->consumerUnit());
        self::assertSame(682, $articleCreated->unitPrice()->toInt());
        self::assertSame(0.055, $articleCreated->tax()->rate());
        self::assertSame('TVA taux réduit', $articleCreated->tax()->name()->toString());
        self::assertSame(8.8, $articleCreated->minStock());
        $zoneStorages = $articleCreated->zoneStorages()->toArray();
        $firstZoneStorage = $zoneStorages[0];
        self::assertSame('Réserve froide', $firstZoneStorage->label()->toString());
        self::assertSame('Frais', $firstZoneStorage->familyLog()->label()->toString());
        self::assertSame('Viande', $articleCreated->familyLog()->label()->toString());
        self::assertSame(12.500, $articleCreated->quantity()->toFloat());
        self::assertSame('jambon-trad-6kg', $articleCreated->slug());
        self::assertTrue($articleCreated->active());
    }

    public function testCreateArticleFailWithAlreadyExistsException(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        // Créer la configuration avec Foundry
        CompanyFactory::createOne();
        $tax = TaxFactory::createOne();
        $familyLog = FamilyLogFactory::createOne();
        $zoneStorage = ZoneStorageFactory::createOne(['familyLog' => $familyLog]);
        $supplier = SupplierFactory::createOne(['familyLog' => $familyLog]);

        $colis = UnitFactory::createOne(['label' => 'Colis']);
        $piece = UnitFactory::createOne(['label' => 'Pièce']);
        $kilogramme = UnitFactory::createOne(['label' => 'Kilogramme']);

        // Créer un article existant avec ArticleFactory
        ArticleFactory::createOne([
            'name' => 'Jambon Trad 6kg',
            'supplier' => $supplier,
            'tax' => $tax,
            'familyLog' => $familyLog,
            'zoneStorages' => [$zoneStorage],
            'packaging' => [[$colis->_real()->toDomain(), 1.0], null, null],
            'unitPrice' => 682,
        ]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_ARTICLE_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.article.create.titlePage'));

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createArticle[name]' => 'Jambon Trad 6kg',
            'createArticle[supplier]' => $supplier->_real()->uuid(),
            'createArticle[packaging][parcel][unit]' => $colis->_real()->uuid(),
            'createArticle[packaging][parcel][quantity]' => 1,
            'createArticle[packaging][subPackage][unit]' => $piece->_real()->uuid(),
            'createArticle[packaging][subPackage][quantity]' => 2,
            'createArticle[packaging][consumeUnit][unit]' => $kilogramme->_real()->uuid(),
            'createArticle[packaging][consumeUnit][quantity]' => 6.000,
            'createArticle[unitPrice]' => 6.82,
            'createArticle[tax]' => $tax->_real()->uuid(),
            'createArticle[minStock]' => 8.8,
            'createArticle[zoneStorages]' => [$zoneStorage->_real()->uuid()],
            'createArticle[familyLog]' => $familyLog->_real()->uuid(),
            'createArticle[quantity]' => 12.500,
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/articles');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertEquals(ArticleAlreadyExists::MESSAGE, $flash);
    }

    public function testCreateArticleFailWithNoSupplierRegisteredException(): void
    {
        // Arrange - Créer config minimale SANS supplier
        CompanyFactory::createOne(['name' => 'Test company']);
        TaxFactory::createOne(['name' => 'TVA taux réduit', 'rate' => 5.5]);
        $familyLog = FamilyLogFactory::createOne(['label' => 'Surgelé']);
        ZoneStorageFactory::createOne(['label' => 'Reserve froide', 'familyLog' => $familyLog]);

        // Act
        $this->client->request(Request::METHOD_GET, self::CREATE_ARTICLE_URI);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/configure');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertEquals(NoSupplierRegistered::MESSAGE, $flash);
    }

    public function testCreateArticleFailWithInvalidFamilyLogAgainstSupplier(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        // Créer la configuration de base
        CompanyFactory::createOne();
        $tax = TaxFactory::createOne();
        $colis = UnitFactory::createOne(['label' => 'Colis']);
        $piece = UnitFactory::createOne(['label' => 'Pièce']);
        $kilogramme = UnitFactory::createOne(['label' => 'Kilogramme']);

        // Créer FamilyLog et Supplier (supplier lié à "Alimentaire")
        $familyLog0 = FamilyLogFactory::createOne(['label' => 'Alimentaire']);
        $familyLog1 = FamilyLogFactory::createOne(['label' => 'Frais', 'parent' => $familyLog0->_real()]);
        $zoneStorage = ZoneStorageFactory::createOne(['familyLog' => $familyLog1]);
        $supplier = SupplierFactory::createOne(['familyLog' => $familyLog0]);

        // Créer un FamilyLog incompatible (pas lié à Alimentaire)
        $familyLog2 = FamilyLogFactory::createOne(['label' => 'Viande']);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_ARTICLE_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.article.create.titlePage'));

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createArticle[name]' => 'Jambon Trad 6kg',
            'createArticle[supplier]' => $supplier->_real()->uuid(),
            'createArticle[packaging][parcel][unit]' => $colis->_real()->uuid(),
            'createArticle[packaging][parcel][quantity]' => 1,
            'createArticle[packaging][subPackage][unit]' => $piece->_real()->uuid(),
            'createArticle[packaging][subPackage][quantity]' => 2,
            'createArticle[packaging][consumeUnit][unit]' => $kilogramme->_real()->uuid(),
            'createArticle[packaging][consumeUnit][quantity]' => 6.800,
            'createArticle[unitPrice]' => 6.82,
            'createArticle[tax]' => $tax->_real()->uuid(),
            'createArticle[minStock]' => 8.8,
            'createArticle[zoneStorages]' => [$zoneStorage->_real()->uuid()],
            'createArticle[familyLog]' => $familyLog2->_real()->uuid(),
            'createArticle[quantity]' => 12.500,
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response = $this->client->getCrawler();

        $zoneStorageField = $response->filter('form')->children('div')->eq(4)->children('div');
        $familyLogField = $zoneStorageField->siblings();

        self::assertSame(
            $translator->trans('admin.article.form.familyLog.label'),
            $familyLogField->children('label')->text()
        );
        self::assertSame(
            'Le champ Famille logistique "Viande" n\'est pas compatible avec la famille logistique '
            . 'du fournisseur: "Alimentaire"',
            $familyLogField->children('ul > li')->text()
        );
    }

    public function testCreateArticleFailWithInvalidZoneStorageAgainstSupplier(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        // Créer la configuration de base
        CompanyFactory::createOne();
        $tax = TaxFactory::createOne();
        $colis = UnitFactory::createOne(['label' => 'Colis']);
        $piece = UnitFactory::createOne(['label' => 'Pièce']);
        $kilogramme = UnitFactory::createOne(['label' => 'Kilogramme']);

        // Créer FamilyLog et Supplier (supplier lié à "Alimentaire")
        $familyLog0 = FamilyLogFactory::createOne(['label' => 'Alimentaire']);
        $familyLog1 = FamilyLogFactory::createOne(['label' => 'Frais', 'parent' => $familyLog0->_real()]);
        $supplier = SupplierFactory::createOne(['familyLog' => $familyLog0]);

        // Créer ZoneStorage incompatible (lié à "Viande" au lieu de "Alimentaire")
        $incompatibleFamilyLog = FamilyLogFactory::createOne(['label' => 'Viande']);
        $zoneStorage = ZoneStorageFactory::createOne(['label' => 'Réserve froide', 'familyLog' => $incompatibleFamilyLog]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_ARTICLE_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.article.create.titlePage'));

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createArticle[name]' => 'Jambon Trad 6kg',
            'createArticle[supplier]' => $supplier->_real()->uuid(),
            'createArticle[packaging][parcel][unit]' => $colis->_real()->uuid(),
            'createArticle[packaging][parcel][quantity]' => 1,
            'createArticle[packaging][subPackage][unit]' => $piece->_real()->uuid(),
            'createArticle[packaging][subPackage][quantity]' => 2,
            'createArticle[packaging][consumeUnit][unit]' => $kilogramme->_real()->uuid(),
            'createArticle[packaging][consumeUnit][quantity]' => 6.800,
            'createArticle[unitPrice]' => 6.82,
            'createArticle[tax]' => $tax->_real()->uuid(),
            'createArticle[minStock]' => 8.8,
            'createArticle[zoneStorages]' => [$zoneStorage->_real()->uuid()],
            'createArticle[familyLog]' => $familyLog1->_real()->uuid(),
            'createArticle[quantity]' => 12.500,
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response = $this->client->getCrawler();

        $zoneStorageField = $response->filter('form')->children('div')->eq(4)->children('div');

        self::assertSame(
            $translator->trans('admin.article.form.zoneStorages.label'),
            $zoneStorageField->children('label')->text()
        );
        self::assertSame(
            'Le champ Zones de stockage "Frais" n\'est pas compatible avec la famille logistique '
            . 'du fournisseur: "Viande"',
            $zoneStorageField->children('ul > li')->text()
        );
    }
}
