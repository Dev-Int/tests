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

namespace Admin\Tests\Adapters\controller\Symfony\Controller\Article\ChangeArticleStorageInformation;

use Admin\Adapters\Gateway\ORM\Entity\Article\Article;
use Admin\Adapters\Gateway\ORM\Entity\Unit;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineArticleRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineSupplierRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineTaxRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineUnitRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineZoneStorageRepository;
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
class ChangeArticleStorageInformationControllerTest extends WebTestCase
{
    public const CHANGE_ARTICLE_STORAGE_INFORMATION_URI = '/admin/articles/%s/change-article-storage-information';

    public function testChangeArticleStorageInformationWillSucceed(): void
    {
        // Arrange
        $client = static::createClient();

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
        $piece = (new UnitDataBuilder())
            ->create('Pièce', 'kg')
            ->withUuid('eca51cd2-4189-4a55-be7e-a6928cf1b5a8')
            ->build()
        ;
        $kilogramme = (new UnitDataBuilder())
            ->create('Kilogramme', 'kg')
            ->withUuid('f016bde4-f36e-468b-bac0-af2b76a9d496')
            ->build()
        ;
        $unitRepository->save($colis);
        $unitRepository->save($piece);
        $unitRepository->save($kilogramme);

        $tax = (new TaxDataBuilder())->create('TVA taux réduit', 5.5)->build();
        $taxRepository->save($tax);

        $surgele = (new FamilyLogDataBuilder())->create('Surgelé')->build();
        $frais = (new FamilyLogDataBuilder())
            ->create('Frais')
            ->withUuid('99282a8d-f344-456c-bbd3-37fe89f3876c')
            ->build()
        ;
        $familyLogRepository->save($surgele);
        $familyLogRepository->save($frais);

        $storageSurgele = (new ZoneStorageDataBuilder())->create('Réserve négative', $surgele)->build();
        $zoneStorageRepository->save($storageSurgele);

        $supplierSurgele = (new SupplierDataBuilder())->create('Supplier Surgelé', $frais)->build();
        $supplierRepository->save($supplierSurgele);

        $article = (new ArticleDataBuilder())
            ->create(
                'Jambon Trad 6kg',
                $supplierSurgele,
                $tax,
                [$storageSurgele],
                $frais,
                [[$colis, 1.0], null, null]
            )
            ->build()
        ;
        $articleRepository->save($article);

        // Act
        $crawler = $client->request(
            Request::METHOD_GET,
            sprintf(self::CHANGE_ARTICLE_STORAGE_INFORMATION_URI, $article->slug())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Change storage information to "Jambon Trad 6kg"');

        $form = $crawler->selectButton('Update')->form([
            'changeArticleStorageInformation[packaging][parcel][unit]' => $colis->uuid()->toString(),
            'changeArticleStorageInformation[packaging][parcel][quantity]' => 1,
            'changeArticleStorageInformation[packaging][subPackage][unit]' => $piece->uuid()->toString(),
            'changeArticleStorageInformation[packaging][subPackage][quantity]' => 2,
            'changeArticleStorageInformation[packaging][consumeUnit][unit]' => $kilogramme->uuid()->toString(),
            'changeArticleStorageInformation[packaging][consumeUnit][quantity]' => 6.800,
            'changeArticleStorageInformation[minStock]' => 6.8,
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/articles');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container')->children('div.flash.flash-success')->text();

        static::assertEquals('Article updated', $flash);

        $articleUpdated = $articleRepository->findOneBy(['slug' => 'jambon-trad-6kg']);
        static::assertInstanceOf(Article::class, $articleUpdated);
        $colisOrm = $unitRepository->findOneBy(['slug' => $colis->slug()]);
        static::assertInstanceOf(Unit::class, $colisOrm);
        $pieceOrm = $unitRepository->findOneBy(['slug' => $piece->slug()]);
        static::assertInstanceOf(Unit::class, $pieceOrm);
        $kilogrammeOrm = $unitRepository->findOneBy(['slug' => $kilogramme->slug()]);
        static::assertInstanceOf(Unit::class, $kilogrammeOrm);
        static::assertSame($colisOrm, $articleUpdated->packaging()->parcelUnit());
        static::assertSame(1.0, $articleUpdated->packaging()->parcelQuantity());
        static::assertSame($pieceOrm, $articleUpdated->packaging()->subPackageUnit());
        static::assertSame(2.0, $articleUpdated->packaging()->subPackageQuantity());
        static::assertSame($kilogrammeOrm, $articleUpdated->packaging()->consumeUnitUnit());
        static::assertSame(6.800, $articleUpdated->packaging()->consumeUnitQuantity());
        static::assertSame(6.8, $articleUpdated->minStock());
    }
}
