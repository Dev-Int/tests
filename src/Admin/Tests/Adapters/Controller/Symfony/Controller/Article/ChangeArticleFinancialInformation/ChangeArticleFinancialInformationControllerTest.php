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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\Article\ChangeArticleFinancialInformation;

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

final class ChangeArticleFinancialInformationControllerTest extends BaseFunctionalTestCase
{
    use Factories;

    public const string CHANGE_ARTICLE_FINANCIAL_INFORMATION_URI = '/admin/articles/%s/change-financial-information';

    public function testChangeArticleFinancialInformationWillSucceed(): void
    {
        // Arrange
        /** @var ArticleRepository $articleRepository */
        $articleRepository = self::getContainer()->get(ArticleRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        // Créer un article avec Foundry
        $tax = TaxFactory::createOne();
        $familyLog = FamilyLogFactory::createOne();
        $supplier = SupplierFactory::createOne(['familyLog' => $familyLog]);
        $zoneStorage = ZoneStorageFactory::createOne(['familyLog' => $familyLog]);
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

        // Créer une taxe supplémentaire pour le changement
        $tax55 = TaxFactory::createOne([
            'name' => 'TVA taux réduit',
            'rate' => 5.5,
        ])->_real()->toDomain();

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_ARTICLE_FINANCIAL_INFORMATION_URI, $article->uuid()->toString())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans(
                'admin.article.changeFinancialInformation.titlePage',
                ['%articleName%' => $article->name()->toString()]
            )
        );

        $form = $crawler->selectButton($translator->trans('admin.article.changeFinancialInformation.button'))->form([
            'changeArticleFinancialInformation[unitPrice]' => 7.25,
            'changeArticleFinancialInformation[tax]' => $tax55->uuid()->toString(),
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/articles');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertEquals($translator->trans('admin.article.changeFinancialInformation.success'), $flash);

        $articleUpdated = $articleRepository->getByUuid($article->uuid());
        self::assertSame(725, $articleUpdated->unitPrice()->toInt());
        self::assertSame(0.055, $articleUpdated->tax()->rate());
    }

    public function testChangeArticleFinancialInformationFailWithArticleNotFoundException(): void
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
            \sprintf(self::CHANGE_ARTICLE_FINANCIAL_INFORMATION_URI, $faker->uuid())
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $response = $this->client->getCrawler();
        $title = $response->filter('h1')->text();

        self::assertEquals('Page non trouvée', $title);
    }
}
