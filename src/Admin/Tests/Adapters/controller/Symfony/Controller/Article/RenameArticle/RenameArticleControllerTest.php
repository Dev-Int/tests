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

namespace Admin\Tests\Adapters\controller\Symfony\Controller\Article\RenameArticle;

use Admin\Adapters\Gateway\ORM\Entity\Article\Article;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineArticleRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineSupplierRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineTaxRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineUnitRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineZoneStorageRepository;
use Admin\Entities\Exception\ArticleAlreadyExistsException;
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
final class RenameArticleControllerTest extends WebTestCase
{
    private const RENAME_ARTICLE_URI = '/admin/articles/%s/rename';

    public function testRenameArticleWillSucceed(): void
    {
        // Arrange
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

        $tax = (new TaxDataBuilder())->create('TVA taux réduit', 5.5)->build();
        $taxRepository->save($tax);

        $familyLog = (new FamilyLogDataBuilder())->create('Frais')
            ->withUuid('99282a8d-f344-456c-bbd3-37fe89f3876c')
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
        $crawler = $client->request(
            Request::METHOD_GET,
            sprintf(self::RENAME_ARTICLE_URI, $article->uuid()->toString())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Rename "Jambon Trad 6kg"');

        $form = $crawler->selectButton('Rename')->form([
            'renameArticle[name]' => 'Jambon 6kg',
            'renameArticle[uuid]' => $article->uuid()->toString(),
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/articles');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertEquals('Article updated', $flash);

        $articleUpdated = $articleRepository->find($article->uuid()->toString());
        self::assertInstanceOf(Article::class, $articleUpdated);
        self::assertSame('Jambon 6kg', $articleUpdated->name());
    }

    public function testRenameArticleFailWithAlreadyExistsException(): void
    {
        // Arrange
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

        $tax = (new TaxDataBuilder())->create('TVA taux réduit', 5.5)->build();
        $taxRepository->save($tax);

        $familyLog = (new FamilyLogDataBuilder())->create('Frais')
            ->withUuid('99282a8d-f344-456c-bbd3-37fe89f3876c')
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
            ->withUuid('f016bde4-f36e-468b-bac0-af2b76a9d496')
            ->build()
        ;
        $articleRepository->save($article2);

        // Act
        $crawler = $client->request(
            Request::METHOD_GET,
            sprintf(self::RENAME_ARTICLE_URI, $article1->uuid()->toString())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Rename "Jambon Trad 6kg"');

        $form = $crawler->selectButton('Rename')->form([
            'renameArticle[name]' => 'Jambon 6kg',
            'renameArticle[uuid]' => $article1->uuid()->toString(),
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/articles');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertEquals(ArticleAlreadyExistsException::MESSAGE, $flash);
    }
}
