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
use FakerRestaurant\Provider\fr_FR\Restaurant;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group functionalTest
 */
final class GetArticlesControllerTest extends BaseFunctionalTestCase
{
    private const GET_ARTICLES_URI = '/admin/articles';

    public function testGetArticlesPaginatedWillSucceed(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $faker->addProvider(new Restaurant($faker));

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

        $tax = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $taxRepository->save($tax);

        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')->build();
        $familyLogRepository->save($familyLog);

        $zoneStorage = (new ZoneStorageDataBuilder())
            ->create('Réserve négative', $familyLog)
            ->build()
        ;
        $zoneStorageRepository->save($zoneStorage);

        $supplier = (new SupplierDataBuilder())->create('supplier 1', $familyLog)->build();
        $supplierRepository->save($supplier);

        $articleDataBuilder = new ArticleDataBuilder();
        for ($i = 0; $i < 15; $i++) {
            $article1 = $articleDataBuilder
                ->create(
                    $faker->vegetableName(),
                    $supplier,
                    $tax,
                    [$zoneStorage],
                    $familyLog,
                    [[$colis, 1.0], null, null]
                )
                ->withUuid($faker->uuid())
                ->build()
            ;
            $articleRepository->save($article1);
        }
        for ($i = 0; $i < 15; $i++) {
            $article2 = $articleDataBuilder
                ->create(
                    $faker->meatName(),
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
