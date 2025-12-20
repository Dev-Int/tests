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

use Admin\Entities\Exception\Article\ArticleAlreadyExists;
use Admin\Entities\Repository\ArticleRepository;
use Admin\Tests\Factory\ArticleFactory;
use Admin\Tests\Factory\FamilyLogFactory;
use Admin\Tests\Factory\SupplierFactory;
use Admin\Tests\Factory\TaxFactory;
use Admin\Tests\Factory\UnitFactory;
use Admin\Tests\Factory\ZoneStorageFactory;
use App\Shared\Tests\BaseFunctionalTestCase;
use Faker\Factory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 *
 * @covers \Admin\Adapters\Controller\Symfony\Controller\Article\RenameArticle\RenameArticleController
 */
final class RenameArticleControllerTest extends BaseFunctionalTestCase
{
    use Factories;

    private const string RENAME_ARTICLE_URI = '/admin/articles/%s/rename';

    public function testRenameArticleWillSucceed(): void
    {
        // Arrange
        /** @var ArticleRepository $articleRepository */
        $articleRepository = self::getContainer()->get(ArticleRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        // Créer la configuration de base
        $tax = TaxFactory::createOne();
        $familyLog = FamilyLogFactory::createOne();
        $zoneStorage = ZoneStorageFactory::createOne(['familyLog' => $familyLog]);
        $supplier = SupplierFactory::createOne(['familyLog' => $familyLog]);
        $unit = UnitFactory::createOne();

        $articleProxy = ArticleFactory::createOne([
            'name' => 'Jambon Trad 6kg',
            'supplier' => $supplier,
            'tax' => $tax,
            'familyLog' => $familyLog,
            'zoneStorages' => [$zoneStorage],
            'packaging' => [[$unit->_real()->toDomain(), 1.0], null, null],
        ]);
        $article = $articleProxy->_real()->toDomain();

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

        $articleUpdated = $articleRepository->getByUuid($article->uuid());
        self::assertSame('Jambon 6kg', $articleUpdated->name()->toString());
    }

    public function testRenameArticleFailWithAlreadyExistsException(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        // Créer la configuration de base
        $tax = TaxFactory::createOne();
        $familyLog = FamilyLogFactory::createOne();
        $zoneStorage = ZoneStorageFactory::createOne(['familyLog' => $familyLog]);
        $supplier = SupplierFactory::createOne(['familyLog' => $familyLog]);
        $unit = UnitFactory::createOne();

        // Créer le premier article
        $articleProxy1 = ArticleFactory::createOne([
            'name' => 'Jambon Trad 6kg',
            'supplier' => $supplier,
            'tax' => $tax,
            'familyLog' => $familyLog,
            'zoneStorages' => [$zoneStorage],
            'packaging' => [[$unit->_real()->toDomain(), 1.0], null, null],
        ]);
        $article1 = $articleProxy1->_real()->toDomain();

        // Créer un 2ème article avec un nom différent
        ArticleFactory::createOne([
            'name' => 'Jambon 6kg',
            'supplier' => $supplier,
            'tax' => $tax,
            'familyLog' => $familyLog,
            'zoneStorages' => [$zoneStorage],
            'packaging' => [[$unit->_real()->toDomain(), 1.0], null, null],
            'unitPrice' => 682,
        ]);

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

        self::assertEquals(ArticleAlreadyExists::MESSAGE, $flash);
    }

    public function testRenameArticleFailWithArticleNotFound(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

        // Créer un article pour avoir des données en base (test realiste)
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
            \sprintf(self::RENAME_ARTICLE_URI, $faker->uuid())
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $response = $this->client->getCrawler();

        $title = $response->filter('h1')->text();

        self::assertEquals('Page non trouvée', $title);
    }
}
