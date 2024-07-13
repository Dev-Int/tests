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

namespace Admin\Tests\Adapters\controller\Symfony\Controller\Article\GetArticles;

use Admin\Adapters\Gateway\ORM\Repository\DoctrineArticleRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineSupplierRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineTaxRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineUnitRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineZoneStorageRepository;
use Admin\Entities\Exception\NoArticleRegisteredException;
use Admin\Tests\DataBuilder\ArticleDataBuilder;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use Admin\Tests\DataBuilder\ZoneStorageDataBuilder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group functionalTest
 */
final class GetArticlesControllerTest extends WebTestCase
{
    private const GET_ARTICLES_URI = '/admin/articles';

    public function testGetArticlesWillSucceed(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineArticleRepository $articleRepository */
        $articleRepository = self::getContainer()->get(DoctrineArticleRepository::class);

        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(DoctrineUnitRepository::class);
        $colis = (new UnitDataBuilder())->create('Colis', 'kg')->build();
        $unitRepository->save($colis);

        /** @var DoctrineTaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(DoctrineTaxRepository::class);
        $tax = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $taxRepository->save($tax);

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);
        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')->build();
        $familyLogRepository->save($familyLog);

        /** @var DoctrineZoneStorageRepository $zoneStorageRepository */
        $zoneStorageRepository = self::getContainer()->get(DoctrineZoneStorageRepository::class);
        $zoneStorage = (new ZoneStorageDataBuilder())
            ->create('Réserve négative', $familyLog)
            ->build()
        ;
        $zoneStorageRepository->save($zoneStorage);

        /** @var DoctrineSupplierRepository $supplierRepository */
        $supplierRepository = self::getContainer()->get(DoctrineSupplierRepository::class);
        $supplier = (new SupplierDataBuilder())->create('supplier 1', $familyLog)->build();
        $supplierRepository->save($supplier);

        $articleDataBuilder = new ArticleDataBuilder();
        $article1 = $articleDataBuilder
            ->create(
                'Jambon Trad 6kg',
                $supplier,
                $tax,
                [$zoneStorage],
                $familyLog,
                [[$colis, 1.0], null, null]
            )
            ->build()
        ;
        $article2 = $articleDataBuilder
            ->create(
                'Jambon Trad 6kg',
                $supplier,
                $tax,
                [$zoneStorage],
                $familyLog,
                [[$colis, 1.0], null, null]
            )
            ->withUuid('99282a8d-f344-456c-bbd3-37fe89f3876c')
            ->build()
        ;
        $articleRepository->save($article1);
        $articleRepository->save($article2);

        // Act
        $crawler = $client->request(Request::METHOD_GET, self::GET_ARTICLES_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Articles');

        $list = $crawler->filter('body > div.container > div.row > article > ul.w100')->children('li.li-unstyled');
        self::assertCount(2, $list);
    }

    public function testGetArticlesFailWithNoArticleRegisteredException(): void
    {
        // Arrange
        $client = self::createClient();

        // Act
        $client->request(Request::METHOD_GET, self::GET_ARTICLES_URI);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/configure');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container')->children('div.flash.flash-error')->text();

        self::assertSame(NoArticleRegisteredException::MESSAGE, $flash);
    }
}
