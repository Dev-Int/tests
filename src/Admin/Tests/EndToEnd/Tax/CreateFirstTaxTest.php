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

namespace Admin\Tests\EndToEnd\Tax;

use Admin\Adapters\Controller\Symfony\Controller\ConfigurationController;
use Admin\Adapters\Controller\Symfony\Controller\Tax\CreateTax\CreateTaxController;
use Admin\Adapters\Controller\Symfony\Controller\Tax\GetTaxes\GetTaxesController;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineCompanyRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineUnitRepository;
use Admin\Tests\DataBuilder\CompanyDataBuilder;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use Faker\Factory;
use Shared\Tests\BasePantherTestCase;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group e2eTest
 */
final class CreateFirstTaxTest extends BasePantherTestCase
{
    public function testCreateFirstTaxSuccessfully(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);

        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(DoctrineUnitRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

        // Créer Company et Unit (prérequis pour créer une Tax)
        $company = (new CompanyDataBuilder())->create(name: $faker->company())->build();
        $companyRepository->save($company);

        $unit = (new UnitDataBuilder())
            ->create('Kilogramme', 'kg')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $unitRepository->save($unit);

        $this->flushAndClearEntityManager();

        // Act && Assert
        $client->request('GET', '/');
        self::assertSelectorTextContains('h1', $translator->trans('home.welcome'));

        $client->clickLink($translator->trans('admin.titlePage'));

        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.configuration.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.configuration.titlePage'));

        $client->clickLink($translator->trans('admin.configuration.application.titlePage'));

        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.configuration.application.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.configuration.application.titlePage'));

        $client->clickLink($translator->trans('admin.tax.create.titleShort'));

        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.tax.create.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.tax.create.titlePage'));

        $client->waitForVisibility('button[type="submit"]');

        $taxName = 'TVA normale';
        $taxRate = 0.2; // 20%

        $client->submitForm($translator->trans('add'), [
            'createTax[name]' => $taxName,
            'createTax[rate]' => $taxRate,
        ]);

        $client->wait(2);
        $getTaxesUrl = $router->generate(GetTaxesController::ROUTE_NAME);
        self::assertStringContainsString($getTaxesUrl, $client->getCurrentURL());

        // Vérifier qu'on a quitté la page de création
        $client->wait(1);
        self::assertStringNotContainsString(
            $router->generate(CreateTaxController::ROUTE_NAME),
            $client->getCurrentURL()
        );
    }

    public function testCancelDuringFirstTaxCreation(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);

        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(DoctrineUnitRepository::class);

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

        $this->flushAndClearEntityManager();

        // Act && Assert
        $client->request('GET', '/');
        self::assertSelectorTextContains('h1', $translator->trans('home.welcome'));

        $client->clickLink($translator->trans('admin.titlePage'));

        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.configuration.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.configuration.titlePage'));

        $client->clickLink($translator->trans('admin.configuration.application.titlePage'));

        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.configuration.application.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.configuration.application.titlePage'));

        $client->clickLink($translator->trans('admin.tax.create.titleShort'));

        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.tax.create.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.tax.create.titlePage'));

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
