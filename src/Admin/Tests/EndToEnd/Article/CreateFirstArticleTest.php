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

namespace Admin\Tests\EndToEnd\Article;

use Admin\Adapters\Controller\Symfony\Controller\Article\CreateArticle\CreateArticleController;
use Admin\Adapters\Controller\Symfony\Controller\Article\GetArticles\GetArticlesController;
use Admin\Adapters\Controller\Symfony\Controller\ConfigurationController;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineCompanyRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineSupplierRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineTaxRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineUnitRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineZoneStorageRepository;
use Admin\Tests\DataBuilder\CompanyDataBuilder;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use Admin\Tests\DataBuilder\ZoneStorageDataBuilder;
use Faker\Factory;
use Shared\Tests\AuthenticatedPantherTestTrait;
use Shared\Tests\BasePantherTestCase;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group e2eTest
 */
final class CreateFirstArticleTest extends BasePantherTestCase
{
    use AuthenticatedPantherTestTrait;

    public function testCreateFirstArticleSuccessfully(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

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

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

        $this->loginViaForm($client, $translator);

        $company = (new CompanyDataBuilder())->create(name: $faker->company())->build();
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

        $tax = (new TaxDataBuilder())
            ->create('TVA normale', 0.2)
            ->withUuid($faker->uuid())
            ->build()
        ;
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

        $this->flushAndClearEntityManager();

        // Act && Assert
        $client->request('GET', '/');
        self::assertSelectorTextContains('h1', $translator->trans('home.welcome'));

        $client->clickLink($translator->trans('admin.titlePage'));
        $client->waitForElementToContain('h1', $translator->trans('admin.configuration.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.configuration.titlePage'));

        $client->clickLink($translator->trans('admin.article.create.titlePage'));
        $client->waitForElementToContain('h1', $translator->trans('admin.article.create.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.article.create.titlePage'));

        $client->waitForVisibility('button[type="submit"]');

        $articleName = 'Article 1';

        self::assertSelectorTextContains(
            'div > ul > li.li-unstyled > label.accordion_label',
            'Alimentaire'
        );
        $client->getCrawler()->filter('#createArticle_familyLog0')->first()->click();
        $client->getCrawler()->filter('#createArticle_familyLog1')->first()->click();

        $client->submitForm($translator->trans('add'), [
            'createArticle[name]' => $articleName,
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
        $client->waitForVisibility('ul.table');
        $getArticlesUrl = $router->generate(GetArticlesController::ROUTE_NAME);
        self::assertStringContainsString($getArticlesUrl, $client->getCurrentURL());

        self::assertSelectorTextContains('ul.table', $articleName);

        // Vérifier qu'on a quitté la page de création
        self::assertStringNotContainsString(
            $router->generate(CreateArticleController::ROUTE_NAME),
            $client->getCurrentURL()
        );
    }

    public function testCancelDuringFirstArticleCreation(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

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

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

        $this->loginViaForm($client, $translator);

        $company = (new CompanyDataBuilder())->create(name: $faker->company())->build();
        $companyRepository->save($company);

        $colis = (new UnitDataBuilder())->create('Colis', 'kg')->build();
        $unitRepository->save($colis);

        $tax = (new TaxDataBuilder())
            ->create('TVA normale', 0.2)
            ->withUuid($faker->uuid())
            ->build()
        ;
        $taxRepository->save($tax);

        $familyLog0 = (new FamilyLogDataBuilder())->create('Alimentaire')->build();
        $familyLog1 = (new FamilyLogDataBuilder())->create('Frais')
            ->withUuid($faker->uuid())
            ->withParent($familyLog0)
            ->build()
        ;
        $familyLogRepository->save($familyLog0);
        $familyLogRepository->save($familyLog1);

        $zoneStorage = (new ZoneStorageDataBuilder())->create('Réserve froide', $familyLog1)->build();
        $zoneStorageRepository->save($zoneStorage);

        $supplier = (new SupplierDataBuilder())->create('Supplier 1', $familyLog0)->build();
        $supplierRepository->save($supplier);

        $this->flushAndClearEntityManager();

        // Act && Assert
        $client->request('GET', '/');
        self::assertSelectorTextContains('h1', $translator->trans('home.welcome'));

        $client->clickLink($translator->trans('admin.titlePage'));
        $client->waitForElementToContain('h1', $translator->trans('admin.configuration.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.configuration.titlePage'));

        $client->clickLink($translator->trans('admin.article.create.titlePage'));
        $client->waitForElementToContain('h1', $translator->trans('admin.article.create.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.article.create.titlePage'));

        $client->waitForVisibility('a[role="button"][aria-label="Cancel"]');

        $cancelButtonSelector = 'a[role="button"][aria-label="Cancel"]';
        self::assertSelectorExists($cancelButtonSelector);
        self::assertSelectorTextContains($cancelButtonSelector, $translator->trans('cancel'));

        $client->clickLink($translator->trans('cancel'));
        // Le Cancel redirige vers GetArticles, qui détecte qu'il n'y a pas d'articles
        // et redirige vers Configuration
        $client->waitForElementToContain('h1', $translator->trans('admin.configuration.titlePage'));
        $configureUrl = $router->generate(ConfigurationController::ROUTE_NAME);
        self::assertStringContainsString($configureUrl, $client->getCurrentURL());
        self::assertSelectorTextContains('h1', $translator->trans('admin.configuration.titlePage'));
    }
}
