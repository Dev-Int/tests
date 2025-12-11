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

namespace Admin\Tests\EndToEnd\ZoneStorage;

use Admin\Adapters\Controller\Symfony\Controller\ConfigurationController;
use Admin\Adapters\Controller\Symfony\Controller\ZoneStorage\CreateZoneStorage\CreateZoneStorageController;
use Admin\Adapters\Controller\Symfony\Controller\ZoneStorage\GetZoneStorages\GetZoneStoragesController;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineCompanyRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineTaxRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineUnitRepository;
use Admin\Tests\DataBuilder\CompanyDataBuilder;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use App\Shared\Tests\BasePantherTestCase;
use Faker\Factory;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group e2eTest
 */
final class CreateFirstZoneStorageTest extends BasePantherTestCase
{
    public function testCreateFirstZoneStorageSuccessfully(): void
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

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

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

        $this->flushAndClearEntityManager();

        // Act && Assert
        $client->request('GET', '/');
        self::assertSelectorTextContains('h1', $translator->trans('home.welcome'));

        $client->clickLink($translator->trans('admin.titlePage'));

        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.configuration.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.configuration.titlePage'));

        $client->clickLink($translator->trans('admin.zoneStorage.create.titlePage'));

        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.zoneStorage.create.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.zoneStorage.create.titlePage'));

        $client->waitForVisibility('button[type="submit"]');

        $zoneStorageLabel = 'Réserve froide';

        $familyLogOrm = $familyLogRepository->getByUuid($familyLog->uuid());

        $client->submitForm($translator->trans('add'), [
            'createZoneStorage[label]' => $zoneStorageLabel,
            'createZoneStorage[familyLog]' => $familyLogOrm->uuid()->toString(),
        ]);

        $client->wait(2);
        $getZoneStoragesUrl = $router->generate(GetZoneStoragesController::ROUTE_NAME);
        self::assertStringContainsString($getZoneStoragesUrl, $client->getCurrentURL());

        // Vérifier qu'on a quitté la page de création
        $client->wait(1);
        self::assertStringNotContainsString(
            $router->generate(CreateZoneStorageController::ROUTE_NAME),
            $client->getCurrentURL()
        );
    }

    public function testCancelDuringFirstZoneStorageCreation(): void
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

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

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

        $this->flushAndClearEntityManager();

        // Act && Assert
        $client->request('GET', '/');
        self::assertSelectorTextContains('h1', $translator->trans('home.welcome'));

        $client->clickLink($translator->trans('admin.titlePage'));

        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.configuration.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.configuration.titlePage'));

        $client->clickLink($translator->trans('admin.zoneStorage.create.titlePage'));

        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.zoneStorage.create.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.zoneStorage.create.titlePage'));

        $client->waitForVisibility('a[role="button"][aria-label="Cancel"]');

        $cancelButtonSelector = 'a[role="button"][aria-label="Cancel"]';
        self::assertSelectorExists($cancelButtonSelector);
        self::assertSelectorTextContains($cancelButtonSelector, $translator->trans('cancel'));

        $client->clickLink($translator->trans('cancel'));

        $client->wait(2);
        $configureUrl = $router->generate(ConfigurationController::ROUTE_NAME);
        self::assertStringContainsString($configureUrl, $client->getCurrentURL());
        self::assertSelectorTextContains('h1', $translator->trans('admin.configuration.titlePage'));
    }
}
