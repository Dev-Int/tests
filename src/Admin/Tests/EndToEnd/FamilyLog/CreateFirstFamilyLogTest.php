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

namespace Admin\Tests\EndToEnd\FamilyLog;

use Admin\Adapters\Controller\Symfony\Controller\ConfigurationController;
use Admin\Adapters\Controller\Symfony\Controller\FamilyLog\CreateFamilyLog\CreateFamilyLogController;
use Admin\Adapters\Controller\Symfony\Controller\FamilyLog\GetFamilyLogs\GetFamilyLogsController;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineCompanyRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineTaxRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineUnitRepository;
use Admin\Tests\DataBuilder\CompanyDataBuilder;
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
final class CreateFirstFamilyLogTest extends BasePantherTestCase
{
    public function testCreateFirstFamilyLogSuccessfully(): void
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

        $this->flushAndClearEntityManager();

        // Act && Assert
        $client->request('GET', '/');
        self::assertSelectorTextContains('h1', $translator->trans('home.welcome'));

        $client->clickLink($translator->trans('admin.titlePage'));

        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.configuration.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.configuration.titlePage'));

        $client->clickLink($translator->trans('admin.familyLog.create.titleShort'));

        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.familyLog.create.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.familyLog.create.titlePage'));

        $client->waitForVisibility('button[type="submit"]');

        $familyLogLabel = 'Surgelé';

        $client->submitForm($translator->trans('add'), [
            'createFamilyLog[label]' => $familyLogLabel,
        ]);

        $client->wait(2);
        $getFamilyLogsUrl = $router->generate(GetFamilyLogsController::ROUTE_NAME);
        self::assertStringContainsString($getFamilyLogsUrl, $client->getCurrentURL());

        // Vérifier que le nouveau family log apparaît dans la liste
        $client->wait(1);
        self::assertSelectorTextContains('ul.table', $familyLogLabel);

        // Vérifier qu'on a quitté la page de création
        $client->wait(1);
        self::assertStringNotContainsString(
            $router->generate(CreateFamilyLogController::ROUTE_NAME),
            $client->getCurrentURL()
        );
    }

    public function testCancelDuringFirstFamilyLogCreation(): void
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

        $this->flushAndClearEntityManager();

        // Act && Assert
        $client->request('GET', '/');
        self::assertSelectorTextContains('h1', $translator->trans('home.welcome'));

        $client->clickLink($translator->trans('admin.titlePage'));

        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.configuration.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.configuration.titlePage'));

        $client->clickLink($translator->trans('admin.familyLog.create.titleShort'));

        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.familyLog.create.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.familyLog.create.titlePage'));

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
