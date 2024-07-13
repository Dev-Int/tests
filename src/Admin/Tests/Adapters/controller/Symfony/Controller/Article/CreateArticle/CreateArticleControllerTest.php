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

namespace Admin\Tests\Adapters\controller\Symfony\Controller\Article\CreateArticle;

use Admin\Adapters\Gateway\ORM\Entity\Article\Article;
use Admin\Adapters\Gateway\ORM\Entity\FamilyLog\FamilyLog;
use Admin\Adapters\Gateway\ORM\Entity\Unit;
use Admin\Adapters\Gateway\ORM\Entity\ZoneStorage;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineArticleRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineCompanyRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineSupplierRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineTaxRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineUnitRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineZoneStorageRepository;
use Admin\Entities\Exception\ArticleAlreadyExistsException;
use Admin\Entities\Exception\NoSupplierRegisteredException;
use Admin\Tests\DataBuilder\ArticleDataBuilder;
use Admin\Tests\DataBuilder\CompanyDataBuilder;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use Admin\Tests\DataBuilder\ZoneStorageDataBuilder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function PHPUnit\Framework\assertInstanceOf;

/**
 * @group functionalTest
 */
final class CreateArticleControllerTest extends WebTestCase
{
    private const CREATE_ARTICLE_URI = '/admin/articles/create';

    public function testCreateArticleWillSucceed(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);

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

        $company = (new CompanyDataBuilder())->create('Test company')->build();
        $companyRepository->save($company);

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

        $familyLog0 = (new FamilyLogDataBuilder())->create('Alimentaire')->build();
        $familyLog1 = (new FamilyLogDataBuilder())->create('Frais')
            ->withUuid('f016bde4-f36e-468b-bac0-af2b76a9d496')
            ->withParent($familyLog0)
            ->build()
        ;
        $familyLog2 = (new FamilyLogDataBuilder())->create('Viande')
            ->withUuid('4fb3318a-fdbd-4c8f-9937-4f5cb59e8352')
            ->withParent($familyLog1)
            ->build()
        ;
        $familyLogRepository->save($familyLog0);
        $familyLogRepository->save($familyLog1);
        $familyLogRepository->save($familyLog2);

        $zoneStorage = (new ZoneStorageDataBuilder())->create('Réserve froide', $familyLog1)->build();
        $zoneStorageRepository->save($zoneStorage);

        $supplier = (new SupplierDataBuilder())->create('Supplier 1', $familyLog0)->build();
        $supplierRepository->save($supplier);

        // Act
        $crawler = $client->request(Request::METHOD_GET, self::CREATE_ARTICLE_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Create Article');

        $form = $crawler->selectButton('Create')->form([
            'createArticle[name]' => 'Jambon Trad 6kg',
            'createArticle[supplier]' => $supplier->uuid()->toString(),
            'createArticle[packaging][parcel][unit]' => $colis->uuid()->toString(),
            'createArticle[packaging][parcel][quantity]' => 1,
            'createArticle[packaging][subPackage][unit]' => $piece->uuid()->toString(),
            'createArticle[packaging][subPackage][quantity]' => 2,
            'createArticle[packaging][consumeUnit][unit]' => $kilogramme->uuid()->toString(),
            'createArticle[packaging][consumeUnit][quantity]' => 6.800,
            'createArticle[amount]' => 6.82,
            'createArticle[tax]' => $tax->uuid()->toString(),
            'createArticle[minStock]' => 8.8,
            'createArticle[zoneStorages]' => [$zoneStorage->uuid()->toString()],
            'createArticle[familyLog]' => $familyLog2->uuid()->toString(),
            'createArticle[quantity]' => 12.500,
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/articles');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container')->children('div.flash.flash-success')->text();

        self::assertEquals('Article created', $flash);

        $articleCreated = $articleRepository->findOneBy(['slug' => 'jambon-trad-6kg']);
        self::assertInstanceOf(Article::class, $articleCreated);
        self::assertSame('Jambon Trad 6kg', $articleCreated->name());
        self::assertSame('Supplier 1', $articleCreated->supplier()->name());
        self::assertSame('Alimentaire', $articleCreated->supplier()->familyLog()->label());
        $colisOrm = $unitRepository->findOneBy(['slug' => $colis->slug()]);
        self::assertInstanceOf(Unit::class, $colisOrm);
        $pieceOrm = $unitRepository->findOneBy(['slug' => $piece->slug()]);
        self::assertInstanceOf(Unit::class, $pieceOrm);
        $kilogrammeOrm = $unitRepository->findOneBy(['slug' => $kilogramme->slug()]);
        self::assertInstanceOf(Unit::class, $kilogrammeOrm);
        self::assertSame($colisOrm, $articleCreated->packaging()->parcelUnit());
        self::assertSame(1.0, $articleCreated->packaging()->parcelQuantity());
        self::assertSame($pieceOrm, $articleCreated->packaging()->subPackageUnit());
        self::assertSame(2.0, $articleCreated->packaging()->subPackageQuantity());
        self::assertSame($kilogrammeOrm, $articleCreated->packaging()->consumeUnitUnit());
        self::assertSame(6.800, $articleCreated->packaging()->consumeUnitQuantity());
        self::assertSame(600, $articleCreated->amount());
        self::assertSame(0.055, $articleCreated->tax()->rate());
        self::assertSame('TVA taux réduit', $articleCreated->tax()->name());
        self::assertSame(8.8, $articleCreated->minStock());
        $zoneStorages = $articleCreated->zoneStorages();
        $firstZoneStorage = $zoneStorages[0];
        self::assertInstanceOf(ZoneStorage::class, $firstZoneStorage);
        self::assertSame('Réserve froide', $firstZoneStorage->label());
        self::assertSame('Frais', $firstZoneStorage->familyLog()->label());
        self::assertSame('Viande', $articleCreated->familyLog()->label());
        self::assertSame(12.500, $articleCreated->quantity());
        self::assertSame('jambon-trad-6kg', $articleCreated->slug());
        self::assertTrue($articleCreated->active());
    }

    public function testCreateArticleFailWithAlreadyExistsException(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);
        $company = (new CompanyDataBuilder())->create('Test company')->build();
        $companyRepository->save($company);

        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(DoctrineUnitRepository::class);
        $colis = (new UnitDataBuilder())->create('Colis', 'kg')->build();
        $piece = (new UnitDataBuilder())->create('Pièce', 'kg')
            ->withUuid('eca51cd2-4189-4a55-be7e-a6928cf1b5a8')
            ->build()
        ;
        $kilogramme = (new UnitDataBuilder())->create('Kilogramme', 'kg')
            ->withUuid('f016bde4-f36e-468b-bac0-af2b76a9d496')
            ->build()
        ;
        $unitRepository->save($colis);
        $unitRepository->save($piece);
        $unitRepository->save($kilogramme);

        /** @var DoctrineTaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(DoctrineTaxRepository::class);
        $tax = (new TaxDataBuilder())->create('TVA taux réduit', 5.5)->build();
        $taxRepository->save($tax);

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);
        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')
            ->withUuid('99282a8d-f344-456c-bbd3-37fe89f3876c')
            ->build()
        ;
        $familyLogRepository->save($familyLog);
        $familyLogOrm = $familyLogRepository->find($familyLog->uuid()->toString());
        assertInstanceOf(FamilyLog::class, $familyLogOrm);

        /** @var DoctrineZoneStorageRepository $zoneStorageRepository */
        $zoneStorageRepository = self::getContainer()->get(DoctrineZoneStorageRepository::class);
        $zoneStorage = (new ZoneStorageDataBuilder())->create('Reserve froide', $familyLog)->build();
        $zoneStorageRepository->save($zoneStorage);

        /** @var DoctrineSupplierRepository $supplierRepository */
        $supplierRepository = self::getContainer()->get(DoctrineSupplierRepository::class);
        $supplier = (new SupplierDataBuilder())->create('Supplier 1', $familyLog)->build();
        $supplierRepository->save($supplier);

        /** @var DoctrineArticleRepository $articleRepository */
        $articleRepository = self::getContainer()->get(DoctrineArticleRepository::class);
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
        $crawler = $client->request(Request::METHOD_GET, self::CREATE_ARTICLE_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Create Article');

        $form = $crawler->selectButton('Create')->form([
            'createArticle[name]' => 'Jambon Trad 6kg',
            'createArticle[supplier]' => $supplier->uuid()->toString(),
            'createArticle[packaging][parcel][unit]' => $colis->uuid()->toString(),
            'createArticle[packaging][parcel][quantity]' => 1,
            'createArticle[packaging][subPackage][unit]' => $piece->uuid()->toString(),
            'createArticle[packaging][subPackage][quantity]' => 2,
            'createArticle[packaging][consumeUnit][unit]' => $kilogramme->uuid()->toString(),
            'createArticle[packaging][consumeUnit][quantity]' => 6.000,
            'createArticle[amount]' => 6.82,
            'createArticle[tax]' => $tax->uuid()->toString(),
            'createArticle[minStock]' => 8.8,
            'createArticle[zoneStorages]' => [$zoneStorage->uuid()->toString()],
            'createArticle[familyLog]' => $familyLog->uuid()->toString(),
            'createArticle[quantity]' => 12.500,
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/articles');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container')->children('div.flash.flash-error')->text();

        self::assertEquals(ArticleAlreadyExistsException::MESSAGE, $flash);
    }

    public function testCreateArticleFailWithNoSupplierRegisteredException(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);
        $company = (new CompanyDataBuilder())->create('Test company')->build();
        $companyRepository->save($company);

        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(DoctrineUnitRepository::class);
        $colis = (new UnitDataBuilder())->create('Colis', 'kg')->build();
        $piece = (new UnitDataBuilder())->create('Pièce', 'kg')
            ->withUuid('eca51cd2-4189-4a55-be7e-a6928cf1b5a8')
            ->build()
        ;
        $kilogramme = (new UnitDataBuilder())->create('Kilogramme', 'kg')
            ->withUuid('f016bde4-f36e-468b-bac0-af2b76a9d496')
            ->build()
        ;
        $unitRepository->save($colis);
        $unitRepository->save($piece);
        $unitRepository->save($kilogramme);

        /** @var DoctrineTaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(DoctrineTaxRepository::class);
        $tax = (new TaxDataBuilder())->create('TVA taux réduit', 5.5)->build();
        $taxRepository->save($tax);

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);
        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')
            ->withUuid('99282a8d-f344-456c-bbd3-37fe89f3876c')
            ->build()
        ;
        $familyLogRepository->save($familyLog);
        $familyLogOrm = $familyLogRepository->find($familyLog->uuid()->toString());
        assertInstanceOf(FamilyLog::class, $familyLogOrm);

        /** @var DoctrineZoneStorageRepository $zoneStorageRepository */
        $zoneStorageRepository = self::getContainer()->get(DoctrineZoneStorageRepository::class);
        $zoneStorage = (new ZoneStorageDataBuilder())->create('Reserve froide', $familyLog)->build();
        $zoneStorageRepository->save($zoneStorage);

        // Act
        $client->request(Request::METHOD_GET, self::CREATE_ARTICLE_URI);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/configure');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container')->children('div.flash.flash-error')->text();

        self::assertEquals(NoSupplierRegisteredException::MESSAGE, $flash);
    }
}
