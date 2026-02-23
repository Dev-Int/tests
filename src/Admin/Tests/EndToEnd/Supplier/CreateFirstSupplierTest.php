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

namespace Admin\Tests\EndToEnd\Supplier;

use Admin\Adapters\Controller\Symfony\Controller\ConfigurationController;
use Admin\Adapters\Controller\Symfony\Controller\Supplier\CreateSupplier\CreateSupplierController;
use Admin\Adapters\Controller\Symfony\Controller\Supplier\GetSuppliers\GetSuppliersController;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineCompanyRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineTaxRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineUnitRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineZoneStorageRepository;
use Admin\Tests\DataBuilder\CompanyDataBuilder;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
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
final class CreateFirstSupplierTest extends BasePantherTestCase
{
    use AuthenticatedPantherTestTrait;

    public function testCreateFirstSupplierSuccessfully(): void
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

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

        $this->loginViaForm($client, $translator);

        $company = (new CompanyDataBuilder())->create(name: $faker->company())->build();
        $companyRepository->save($company);

        $unit = (new UnitDataBuilder())
            ->create('Kilogramme', 'kg')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $unitRepository->save($unit);

        $tax = (new TaxDataBuilder())
            ->create('TVA normale', 0.2)
            ->withUuid($faker->uuid())
            ->build()
        ;
        $taxRepository->save($tax);

        $familyLog = (new FamilyLogDataBuilder())
            ->create('Surgelé')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLogRepository->save($familyLog);

        $zoneStorage = (new ZoneStorageDataBuilder())
            ->create('Réserve froide', $familyLog)
            ->withUuid($faker->uuid())
            ->build()
        ;
        $zoneStorageRepository->save($zoneStorage);

        $this->flushAndClearEntityManager();

        // Act && Assert
        $client->request('GET', '/');
        self::assertSelectorTextContains('h1', $translator->trans('home.welcome'));

        $client->clickLink($translator->trans('admin.titlePage'));
        $client->waitForElementToContain('h1', $translator->trans('admin.configuration.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.configuration.titlePage'));

        $client->clickLink($translator->trans('admin.supplier.create.titlePage'));
        $client->waitForElementToContain('h1', $translator->trans('admin.supplier.create.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.supplier.create.titlePage'));

        $client->waitForVisibility('button[type="submit"]');

        $supplierName = 'Fournisseur Test';

        $familyLogOrm = $familyLogRepository->getByUuid($familyLog->uuid());

        $client->submitForm($translator->trans('add'), [
            'createSupplier[name]' => $supplierName,
            'createSupplier[streetAddress]' => '1 rue de la Paix',
            'createSupplier[postalCode]' => '75001',
            'createSupplier[city]' => 'Paris',
            'createSupplier[country]' => 'France',
            'createSupplier[phone]' => '0123456789',
            'createSupplier[email]' => 'contact@test.fr',
            'createSupplier[contact]' => 'Jean Dupont',
            'createSupplier[cellphone]' => '0612345678',
            'createSupplier[familyLog]' => $familyLogOrm->uuid()->toString(),
            'createSupplier[delayDelivery]' => '2',
            'createSupplier[orderDays]' => [0, 2],  // Lundi, Mercredi
        ]);

        $client->waitForVisibility('ul.table');
        $getSuppliersUrl = $router->generate(GetSuppliersController::ROUTE_NAME);
        self::assertStringContainsString($getSuppliersUrl, $client->getCurrentURL());

        // Vérifier qu'on a quitté la page de création
        self::assertStringNotContainsString(
            $router->generate(CreateSupplierController::ROUTE_NAME),
            $client->getCurrentURL()
        );
    }

    public function testCancelDuringFirstSupplierCreation(): void
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

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

        $this->loginViaForm($client, $translator);

        $company = (new CompanyDataBuilder())->create(name: $faker->company())->build();
        $companyRepository->save($company);

        $unit = (new UnitDataBuilder())
            ->create('Kilogramme', 'kg')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $unitRepository->save($unit);

        $tax = (new TaxDataBuilder())
            ->create('TVA normale', 0.2)
            ->withUuid($faker->uuid())
            ->build()
        ;
        $taxRepository->save($tax);

        $familyLog = (new FamilyLogDataBuilder())
            ->create('Surgelé')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLogRepository->save($familyLog);

        $zoneStorage = (new ZoneStorageDataBuilder())
            ->create('Réserve froide', $familyLog)
            ->withUuid($faker->uuid())
            ->build()
        ;
        $zoneStorageRepository->save($zoneStorage);

        $this->flushAndClearEntityManager();

        // Act && Assert
        $client->request('GET', '/');
        self::assertSelectorTextContains('h1', $translator->trans('home.welcome'));

        $client->clickLink($translator->trans('admin.titlePage'));
        $client->waitForElementToContain('h1', $translator->trans('admin.configuration.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.configuration.titlePage'));

        $client->clickLink($translator->trans('admin.supplier.create.titlePage'));
        $client->waitForElementToContain('h1', $translator->trans('admin.supplier.create.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.supplier.create.titlePage'));

        $client->waitForVisibility('a[role="button"][aria-label="Cancel"]');

        $cancelButtonSelector = 'a[role="button"][aria-label="Cancel"]';
        self::assertSelectorExists($cancelButtonSelector);
        self::assertSelectorTextContains($cancelButtonSelector, $translator->trans('cancel'));

        // Cliquer sur Cancel
        $client->clickLink($translator->trans('cancel'));
        $client->waitForElementToContain('h1', $translator->trans('admin.configuration.titlePage'));
        $configureUrl = $router->generate(ConfigurationController::ROUTE_NAME);
        self::assertStringContainsString($configureUrl, $client->getCurrentURL());
        self::assertSelectorTextContains('h1', $translator->trans('admin.configuration.titlePage'));
    }
}
