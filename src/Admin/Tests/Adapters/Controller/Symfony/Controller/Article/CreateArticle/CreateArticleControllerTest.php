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

use Admin\Adapters\Gateway\ORM\Entity\FamilyLog\FamilyLog;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Entities\Exception\Article\ArticleAlreadyExistsException;
use Admin\Entities\Exception\Supplier\NoSupplierRegisteredException;
use Admin\Tests\DataBuilder\ArticleDataBuilder;
use Admin\Tests\DataBuilder\CompanyDataBuilder;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use Admin\Tests\DataBuilder\ZoneStorageDataBuilder;
use Admin\UseCases\Gateway\ArticleRepository;
use Admin\UseCases\Gateway\CompanyRepository;
use Admin\UseCases\Gateway\FamilyLogRepository;
use Admin\UseCases\Gateway\SupplierRepository;
use Admin\UseCases\Gateway\TaxRepository;
use Admin\UseCases\Gateway\UnitRepository;
use Admin\UseCases\Gateway\ZoneStorageRepository;
use App\Shared\Tests\BaseFunctionalTestCase;
use Faker\Factory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

use function PHPUnit\Framework\assertInstanceOf;

/**
 * @group functionalTest
 */
final class CreateArticleControllerTest extends BaseFunctionalTestCase
{
    private const CREATE_ARTICLE_URI = '/admin/articles/create';

    public function testCreateArticleWillSucceed(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

        /** @var CompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(CompanyRepository::class);

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

        $company = (new CompanyDataBuilder())->create('Test company')->build();
        $companyRepository->save($company);

        $colis = (new UnitDataBuilder())->create('Colis', 'kg')->build();
        $piece = (new UnitDataBuilder())
            ->create('Pièce', 'kg')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $kilogramme = (new UnitDataBuilder())
            ->create('Kilogramme', 'kg')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $unitRepository->save($colis);
        $unitRepository->save($piece);
        $unitRepository->save($kilogramme);

        $tax = (new TaxDataBuilder())->create('TVA taux réduit', 5.5)->build();
        $taxRepository->save($tax);

        $familyLog0 = (new FamilyLogDataBuilder())->create('Alimentaire')->build();
        $familyLog1 = (new FamilyLogDataBuilder())->create('Frais')
            ->withUuid($faker->uuid())
            ->withParent($familyLog0)
            ->build()
        ;
        $familyLog2 = (new FamilyLogDataBuilder())->create('Viande')
            ->withUuid($faker->uuid())
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
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_ARTICLE_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.article.create.titlePage'));

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createArticle[name]' => 'Jambon Trad 6kg',
            'createArticle[supplier]' => $supplier->uuid()->toString(),
            'createArticle[packaging][parcel][unit]' => $colis->uuid()->toString(),
            'createArticle[packaging][parcel][quantity]' => 1,
            'createArticle[packaging][subPackage][unit]' => $piece->uuid()->toString(),
            'createArticle[packaging][subPackage][quantity]' => 2,
            'createArticle[packaging][consumeUnit][unit]' => $kilogramme->uuid()->toString(),
            'createArticle[packaging][consumeUnit][quantity]' => 6.800,
            'createArticle[unitPrice]' => 6.82,
            'createArticle[tax]' => $tax->uuid()->toString(),
            'createArticle[minStock]' => 8.8,
            'createArticle[zoneStorages]' => [$zoneStorage->uuid()->toString()],
            'createArticle[familyLog]' => $familyLog2->uuid()->toString(),
            'createArticle[quantity]' => 12.500,
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/articles');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertEquals($translator->trans('admin.article.create.success'), $flash);

        $articleCreated = $articleRepository->findBySlug('jambon-trad-6kg');
        self::assertSame('Jambon Trad 6kg', $articleCreated->name()->toString());
        self::assertSame('Supplier 1', $articleCreated->supplier()->name()->toString());
        self::assertSame('Alimentaire', $articleCreated->supplier()->familyLog()->label()->toString());
        self::assertEquals([$colis, 1.0], $articleCreated->packaging()->parcel());
        self::assertEquals([$piece, 2.0], $articleCreated->packaging()->subPackage());
        self::assertEquals([$kilogramme, 6.800], $articleCreated->packaging()->consumerUnit());
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
        $faker = Factory::create('fr_FR');

        /** @var CompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(CompanyRepository::class);

        /** @var UnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(UnitRepository::class);

        /** @var TaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(TaxRepository::class);

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);

        /** @var ZoneStorageRepository $zoneStorageRepository */
        $zoneStorageRepository = self::getContainer()->get(ZoneStorageRepository::class);

        /** @var SupplierRepository $supplierRepository */
        $supplierRepository = self::getContainer()->get(SupplierRepository::class);

        /** @var ArticleRepository $articleRepository */
        $articleRepository = self::getContainer()->get(ArticleRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $company = (new CompanyDataBuilder())->create('Test company')->build();
        $companyRepository->save($company);

        $colis = (new UnitDataBuilder())->create('Colis', 'kg')->build();
        $piece = (new UnitDataBuilder())->create('Pièce', 'kg')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $kilogramme = (new UnitDataBuilder())->create('Kilogramme', 'kg')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $unitRepository->save($colis);
        $unitRepository->save($piece);
        $unitRepository->save($kilogramme);

        $tax = (new TaxDataBuilder())->create('TVA taux réduit', 5.5)->build();
        $taxRepository->save($tax);

        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLogRepository->save($familyLog);
        $familyLogOrm = $familyLogRepository->find($familyLog->uuid()->toString());
        assertInstanceOf(FamilyLog::class, $familyLogOrm);

        $zoneStorage = (new ZoneStorageDataBuilder())->create('Reserve froide', $familyLog)->build();
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
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_ARTICLE_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.article.create.titlePage'));

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createArticle[name]' => 'Jambon Trad 6kg',
            'createArticle[supplier]' => $supplier->uuid()->toString(),
            'createArticle[packaging][parcel][unit]' => $colis->uuid()->toString(),
            'createArticle[packaging][parcel][quantity]' => 1,
            'createArticle[packaging][subPackage][unit]' => $piece->uuid()->toString(),
            'createArticle[packaging][subPackage][quantity]' => 2,
            'createArticle[packaging][consumeUnit][unit]' => $kilogramme->uuid()->toString(),
            'createArticle[packaging][consumeUnit][quantity]' => 6.000,
            'createArticle[unitPrice]' => 6.82,
            'createArticle[tax]' => $tax->uuid()->toString(),
            'createArticle[minStock]' => 8.8,
            'createArticle[zoneStorages]' => [$zoneStorage->uuid()->toString()],
            'createArticle[familyLog]' => $familyLog->uuid()->toString(),
            'createArticle[quantity]' => 12.500,
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/articles');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertEquals(ArticleAlreadyExistsException::MESSAGE, $flash);
    }

    public function testCreateArticleFailWithNoSupplierRegisteredException(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

        /** @var CompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(CompanyRepository::class);

        /** @var UnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(UnitRepository::class);

        /** @var TaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(TaxRepository::class);

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);

        /** @var ZoneStorageRepository $zoneStorageRepository */
        $zoneStorageRepository = self::getContainer()->get(ZoneStorageRepository::class);

        $company = (new CompanyDataBuilder())->create('Test company')->build();
        $companyRepository->save($company);

        $colis = (new UnitDataBuilder())->create('Colis', 'kg')->build();
        $piece = (new UnitDataBuilder())->create('Pièce', 'kg')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $kilogramme = (new UnitDataBuilder())->create('Kilogramme', 'kg')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $unitRepository->save($colis);
        $unitRepository->save($piece);
        $unitRepository->save($kilogramme);

        $tax = (new TaxDataBuilder())->create('TVA taux réduit', 5.5)->build();
        $taxRepository->save($tax);

        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLogRepository->save($familyLog);
        $familyLogOrm = $familyLogRepository->find($familyLog->uuid()->toString());
        assertInstanceOf(FamilyLog::class, $familyLogOrm);

        $zoneStorage = (new ZoneStorageDataBuilder())->create('Reserve froide', $familyLog)->build();
        $zoneStorageRepository->save($zoneStorage);

        // Act
        $this->client->request(Request::METHOD_GET, self::CREATE_ARTICLE_URI);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/configure');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertEquals(NoSupplierRegisteredException::MESSAGE, $flash);
    }

    public function testCreateArticleFailWithInvalidFamilyLogAgainstSupplier(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

        /** @var CompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(CompanyRepository::class);

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

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $company = (new CompanyDataBuilder())->create('Test company')->build();
        $companyRepository->save($company);

        $colis = (new UnitDataBuilder())->create('Colis', 'kg')->build();
        $piece = (new UnitDataBuilder())
            ->create('Pièce', 'kg')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $kilogramme = (new UnitDataBuilder())
            ->create('Kilogramme', 'kg')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $unitRepository->save($colis);
        $unitRepository->save($piece);
        $unitRepository->save($kilogramme);

        $tax = (new TaxDataBuilder())->create('TVA taux réduit', 5.5)->build();
        $taxRepository->save($tax);

        $familyLog0 = (new FamilyLogDataBuilder())->create('Alimentaire')->build();
        $familyLog1 = (new FamilyLogDataBuilder())->create('Frais')
            ->withUuid($faker->uuid())
            ->withParent($familyLog0)
            ->build()
        ;
        $familyLog2 = (new FamilyLogDataBuilder())->create('Viande')
            ->withUuid($faker->uuid())
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
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_ARTICLE_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.article.create.titlePage'));

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createArticle[name]' => 'Jambon Trad 6kg',
            'createArticle[supplier]' => $supplier->uuid()->toString(),
            'createArticle[packaging][parcel][unit]' => $colis->uuid()->toString(),
            'createArticle[packaging][parcel][quantity]' => 1,
            'createArticle[packaging][subPackage][unit]' => $piece->uuid()->toString(),
            'createArticle[packaging][subPackage][quantity]' => 2,
            'createArticle[packaging][consumeUnit][unit]' => $kilogramme->uuid()->toString(),
            'createArticle[packaging][consumeUnit][quantity]' => 6.800,
            'createArticle[unitPrice]' => 6.82,
            'createArticle[tax]' => $tax->uuid()->toString(),
            'createArticle[minStock]' => 8.8,
            'createArticle[zoneStorages]' => [$zoneStorage->uuid()->toString()],
            'createArticle[familyLog]' => $familyLog2->uuid()->toString(),
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
            'Le champ Famille logistique "Viande" n\'est pas compatible avec la famille logistique du fournisseur: "Alimentaire"',
            $familyLogField->children('ul > li')->text()
        );
    }

    public function testCreateArticleFailWithInvalidZoneStorageAgainstSupplier(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

        /** @var CompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(CompanyRepository::class);

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

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $company = (new CompanyDataBuilder())->create('Test company')->build();
        $companyRepository->save($company);

        $colis = (new UnitDataBuilder())->create('Colis', 'kg')->build();
        $piece = (new UnitDataBuilder())
            ->create('Pièce', 'kg')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $kilogramme = (new UnitDataBuilder())
            ->create('Kilogramme', 'kg')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $unitRepository->save($colis);
        $unitRepository->save($piece);
        $unitRepository->save($kilogramme);

        $tax = (new TaxDataBuilder())->create('TVA taux réduit', 5.5)->build();
        $taxRepository->save($tax);

        $familyLog0 = (new FamilyLogDataBuilder())->create('Alimentaire')->build();
        $familyLog1 = (new FamilyLogDataBuilder())->create('Frais')
            ->withUuid($faker->uuid())
            ->withParent($familyLog0)
            ->build()
        ;
        $familyLog2 = (new FamilyLogDataBuilder())->create('Viande')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLogRepository->save($familyLog0);
        $familyLogRepository->save($familyLog1);
        $familyLogRepository->save($familyLog2);

        $zoneStorage = (new ZoneStorageDataBuilder())->create('Réserve froide', $familyLog2)->build();
        $zoneStorageRepository->save($zoneStorage);

        $supplier = (new SupplierDataBuilder())->create('Supplier 1', $familyLog0)->build();
        $supplierRepository->save($supplier);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_ARTICLE_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.article.create.titlePage'));

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createArticle[name]' => 'Jambon Trad 6kg',
            'createArticle[supplier]' => $supplier->uuid()->toString(),
            'createArticle[packaging][parcel][unit]' => $colis->uuid()->toString(),
            'createArticle[packaging][parcel][quantity]' => 1,
            'createArticle[packaging][subPackage][unit]' => $piece->uuid()->toString(),
            'createArticle[packaging][subPackage][quantity]' => 2,
            'createArticle[packaging][consumeUnit][unit]' => $kilogramme->uuid()->toString(),
            'createArticle[packaging][consumeUnit][quantity]' => 6.800,
            'createArticle[unitPrice]' => 6.82,
            'createArticle[tax]' => $tax->uuid()->toString(),
            'createArticle[minStock]' => 8.8,
            'createArticle[zoneStorages]' => [$zoneStorage->uuid()->toString()],
            'createArticle[familyLog]' => $familyLog1->uuid()->toString(),
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
            'Le champ Zones de stockage "Frais" n\'est pas compatible avec la famille logistique du fournisseur: "Viande"',
            $zoneStorageField->children('ul > li')->text()
        );
    }
}
