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

namespace Admin\Tests\EndToEnd\Unit;

use Admin\Adapters\Controller\Symfony\Controller\ConfigurationController;
use Admin\Adapters\Controller\Symfony\Controller\Unit\CreateUnit\CreateUnitController;
use Admin\Adapters\Controller\Symfony\Controller\Unit\GetUnits\GetUnitsController;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineCompanyRepository;
use Admin\Tests\DataBuilder\CompanyDataBuilder;
use App\Shared\Tests\BasePantherTestCase;
use Faker\Factory;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group e2eTest
 */
final class CreateFirstUnitTest extends BasePantherTestCase
{
    public function testCreateFirstUnitSuccessfully(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

        $company = (new CompanyDataBuilder())->create(name: $faker->company())->build();
        $companyRepository->save($company);
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

        $client->clickLink($translator->trans('admin.unit.create.titleShort'));

        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.unit.create.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.unit.create.titlePage'));

        $client->waitForVisibility('button[type="submit"]');

        $unitLabel = 'Litre';
        $unitAbbreviation = 'L';

        $client->submitForm($translator->trans('add'), [
            'createUnit[label]' => $unitLabel,
            'createUnit[abbreviation]' => $unitAbbreviation,
        ]);

        $client->wait(2);
        $getUnitsUrl = $router->generate(GetUnitsController::ROUTE_NAME);
        self::assertStringContainsString($getUnitsUrl, $client->getCurrentURL());

        // Vérifier qu'on a quitté la page de création
        $client->wait(1);
        self::assertStringNotContainsString(
            $router->generate(CreateUnitController::ROUTE_NAME),
            $client->getCurrentURL()
        );
    }

    public function testCancelDuringFirstUnitCreation(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

        $company = (new CompanyDataBuilder())->create(name: $faker->company())->build();
        $companyRepository->save($company);
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

        $client->clickLink($translator->trans('admin.unit.create.titleShort'));

        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.unit.create.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.unit.create.titlePage'));

        $client->waitForVisibility('a[role="button"][aria-label="Cancel"]');

        $cancelButtonSelector = 'a[role="button"][aria-label="Cancel"]';
        self::assertSelectorExists($cancelButtonSelector);
        self::assertSelectorTextContains($cancelButtonSelector, $translator->trans('cancel'));

        // Cliquer sur Cancel
        $client->clickLink($translator->trans('cancel'));

        $client->wait(2);
        $configureUrl = $router->generate(ConfigurationController::ROUTE_NAME);
        self::assertStringContainsString($configureUrl, $client->getCurrentURL());
        self::assertSelectorTextContains('h1', $translator->trans('admin.configuration.titlePage'));
    }
}
