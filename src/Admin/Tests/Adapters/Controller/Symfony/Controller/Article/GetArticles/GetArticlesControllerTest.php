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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\Article\GetArticles;

use Admin\Adapters\Gateway\Pagination\Pagination;
use Admin\Entities\Exception\Article\NoArticleRegisteredException;
use Admin\Tests\Factory\ArticleFactory;
use Admin\Tests\Factory\FamilyLogFactory;
use Admin\Tests\Factory\SupplierFactory;
use Admin\Tests\Factory\TaxFactory;
use Admin\Tests\Factory\UnitFactory;
use Admin\Tests\Factory\ZoneStorageFactory;
use App\Shared\Tests\BaseFunctionalTestCase;
use Faker\Factory;
use FakerRestaurant\Provider\fr_FR\Restaurant;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 */
final class GetArticlesControllerTest extends BaseFunctionalTestCase
{
    use Factories;

    private const GET_ARTICLES_URI = '/admin/articles';

    public function testGetArticlesPaginatedWillSucceed(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $faker->addProvider(new Restaurant($faker));

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        // Créer la configuration directement avec Foundry
        $supplier = SupplierFactory::createOne();
        $tax = TaxFactory::createOne();
        $familyLog = FamilyLogFactory::createOne();
        $zoneStorage = ZoneStorageFactory::createOne();
        $colis = UnitFactory::createOne(['label' => 'Colis']);

        // Créer 30 articles pour tester la pagination (15 légumes + 15 viandes)
        for ($i = 0; $i < 15; $i++) {
            ArticleFactory::createOne([
                'name' => $faker->vegetableName(),
                'supplier' => $supplier,
                'tax' => $tax,
                'zoneStorages' => [$zoneStorage],
                'familyLog' => $familyLog,
                'packaging' => [[$colis->_real()->toDomain(), 1.0], null, null],
            ]);
        }
        for ($i = 0; $i < 15; $i++) {
            ArticleFactory::createOne([
                'name' => $faker->meatName(),
                'supplier' => $supplier,
                'tax' => $tax,
                'zoneStorages' => [$zoneStorage],
                'familyLog' => $familyLog,
                'packaging' => [[$colis->_real()->toDomain(), 1.0], null, null],
            ]);
        }

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::GET_ARTICLES_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.article.titlePage'));

        $list = $crawler->filter('body > div.container > main > article > turbo-frame > ul.table > turbo-frame')
            ->children('li.li-unstyled')
        ;
        self::assertCount(Pagination::DEFAULT_ITEMS_PER_PAGE, $list);
    }

    public function testGetArticlesFailWithNoArticleRegisteredException(): void
    {
        // Arrange && Act
        $this->client->request(Request::METHOD_GET, self::GET_ARTICLES_URI);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/configure');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertSame(NoArticleRegisteredException::MESSAGE, $flash);
    }
}
