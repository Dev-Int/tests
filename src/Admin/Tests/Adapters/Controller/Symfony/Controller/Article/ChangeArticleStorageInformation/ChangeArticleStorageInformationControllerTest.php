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
class ChangeArticleStorageInformationControllerTest extends BaseFunctionalTestCase
{
    use Factories;

    public const string CHANGE_ARTICLE_STORAGE_INFORMATION_URI = '/admin/articles/%s/change-article-storage-information';

    public function testChangeArticleStorageInformationWillSucceed(): void
    {
        // Arrange
        /** @var ArticleRepository $articleRepository */
        $articleRepository = self::getContainer()->get(ArticleRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        // Créer les unités de packaging
        $colis = UnitFactory::createOne(['label' => 'Colis']);
        $piece = UnitFactory::createOne(['label' => 'Pièce']);
        $kilogramme = UnitFactory::createOne(['label' => 'Kilogramme']);

        // Créer l'article
        $tax = TaxFactory::createOne();
        $familyLog = FamilyLogFactory::createOne();
        $supplier = SupplierFactory::createOne(['familyLog' => $familyLog]);
        $zoneStorage = ZoneStorageFactory::createOne(['familyLog' => $familyLog]);

        $articleProxy = ArticleFactory::createOne([
            'name' => 'Jambon Trad 6kg',
            'supplier' => $supplier,
            'tax' => $tax,
            'familyLog' => $familyLog,
            'zoneStorages' => [$zoneStorage],
            'packaging' => [[$colis->_real()->toDomain(), 1.0], null, null],
        ]);
        $article = $articleProxy->_real()->toDomain();

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
            'changeArticleStorageInformation[packaging][parcel][unit]' => $colis->_real()->uuid(),
            'changeArticleStorageInformation[packaging][parcel][quantity]' => 1,
            'changeArticleStorageInformation[packaging][subPackage][unit]' => $piece->_real()->uuid(),
            'changeArticleStorageInformation[packaging][subPackage][quantity]' => 2,
            'changeArticleStorageInformation[packaging][consumeUnit][unit]' => $kilogramme->_real()->uuid(),
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

        $articleUpdated = $articleRepository->getByUuid($article->uuid());
        static::assertEquals([$colis->_real()->toDomain(), 1.0], $articleUpdated->packaging()->parcel());
        static::assertEquals([$piece->_real()->toDomain(), 2.0], $articleUpdated->packaging()->subPackage());
        static::assertEquals([$kilogramme->_real()->toDomain(), 6.800], $articleUpdated->packaging()->consumerUnit());
        static::assertEquals(6.8, $articleUpdated->minStock());
    }

    public function testChangeArticleStorageInformationFailWithArticleNotfoundException(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

        // Créer un article pour avoir des données en base (test réaliste)
        $tax = TaxFactory::createOne();
        $familyLog = FamilyLogFactory::createOne();
        $supplier = SupplierFactory::createOne(['familyLog' => $familyLog]);
        $zoneStorage = ZoneStorageFactory::createOne(['familyLog' => $familyLog]);
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
            \sprintf(self::CHANGE_ARTICLE_STORAGE_INFORMATION_URI, $faker->uuid())
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $response = $this->client->getCrawler();

        $title = $response->filter('h1')->text();

        static::assertEquals('Page non trouvée', $title);
    }
}
