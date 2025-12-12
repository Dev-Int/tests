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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\Unit\ChangeUnitLabel;

use Admin\Adapters\Controller\Symfony\Controller\Unit\GetUnits\GetUnitsController;
use Admin\Entities\Repository\UnitRepository;
use Admin\Entities\Unit\Unit;
use Admin\Tests\Factory\UnitFactory;
use App\Shared\Tests\BaseFunctionalTestCase;
use Faker\Factory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 */
final class ChangeUnitLabelControllerTest extends BaseFunctionalTestCase
{
    use Factories;

    private const CHANGE_LABEL_URI = '/admin/units/%s/change-label';

    public function testChangeLabelWillSucceed(): void
    {
        // Arrange
        /** @var UnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(UnitRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $unit = UnitFactory::createOne(['label' => 'Kilogramme', 'abbreviation' => 'kg']);
        $units = $unitRepository->getAllUnits();
        self::assertCount(1, $units);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_LABEL_URI, $unit->_real()->uuid())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans('admin.unit.changeLabel.titlePage', ['%unitName%' => 'Kilogramme'])
        );

        $form = $crawler->selectButton($translator->trans('admin.unit.changeLabel.button'))->form([
            'changeUnitLabel[label]' => 'Kilogrammes',
            'changeUnitLabel[abbreviation]' => 'kg',
            'changeUnitLabel[slug]' => 'kilogramme',
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/units');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertSame($translator->trans('admin.unit.changeLabel.success'), $flash);

        /** @var Unit $unitUpdated */
        $unitUpdated = $unitRepository->getBySlug('kilogrammes');
        self::assertSame('Kilogrammes', $unitUpdated->label()->toString());
        self::assertSame('kg', $unitUpdated->abbreviation());
        $units = $unitRepository->getAllUnits();
        self::assertCount(1, $units);
    }

    public function testChangeAbbreviationWillSucceed(): void
    {
        // Arrange
        /** @var UnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(UnitRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $unit = UnitFactory::createOne(['label' => 'Kilogramme', 'abbreviation' => 'kg']);
        $units = $unitRepository->getAllUnits();
        self::assertCount(1, $units);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_LABEL_URI, $unit->_real()->uuid())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans('admin.unit.changeLabel.titlePage', ['%unitName%' => 'Kilogramme'])
        );

        $form = $crawler->selectButton($translator->trans('admin.unit.changeLabel.button'))->form([
            'changeUnitLabel[label]' => 'Kilogramme',
            'changeUnitLabel[abbreviation]' => 'KG',
            'changeUnitLabel[slug]' => 'kilogramme',
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/units');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertSame($translator->trans('admin.unit.changeLabel.success'), $flash);

        /** @var Unit $unitUpdated */
        $unitUpdated = $unitRepository->getBySlug('kilogramme');
        self::assertSame('Kilogramme', $unitUpdated->label()->toString());
        self::assertSame('KG', $unitUpdated->abbreviation());
        $units = $unitRepository->getAllUnits();
        self::assertCount(1, $units);
    }

    public function testChangeLabelFailWithAlreadyExistsException(): void
    {
        // Arrange
        /** @var UnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(UnitRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $unit1 = UnitFactory::createOne(['label' => 'Kilogramme', 'abbreviation' => 'kg']);
        UnitFactory::createOne(['label' => 'Litre', 'abbreviation' => 'L']);
        $units = $unitRepository->getAllUnits();
        self::assertCount(2, $units);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_LABEL_URI, $unit1->_real()->uuid())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans('admin.unit.changeLabel.titlePage', ['%unitName%' => 'Kilogramme'])
        );

        $form = $crawler->selectButton($translator->trans('admin.unit.changeLabel.button'))->form([
            'changeUnitLabel[label]' => 'Litre',
            'changeUnitLabel[abbreviation]' => 'kg',
            'changeUnitLabel[slug]' => 'kilogramme',
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/units');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertSame('Unit already exists.', $flash);

        $units = $unitRepository->getAllUnits();
        self::assertCount(2, $units);
    }

    public function testChangeLabelFailWithBadRequestException(): void
    {
        // Arrange
        /** @var UnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(UnitRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $unit = UnitFactory::createOne(['label' => 'Kilogramme', 'abbreviation' => 'kg']);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_LABEL_URI, $unit->_real()->uuid())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans('admin.unit.changeLabel.titlePage', ['%unitName%' => 'Kilogramme'])
        );

        $form = $crawler->selectButton($translator->trans('admin.unit.changeLabel.button'))->form([
            'changeUnitLabel[label]' => '',
            'changeUnitLabel[abbreviation]' => '',
            'changeUnitLabel[slug]' => 'kilogramme',
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response = $this->client->getCrawler();

        $labelField = $response->filter('form')->children('div')->first();
        $abbreviationField = $labelField->siblings();

        self::assertSame(
            $translator->trans('admin.unit.form.label.placeholder'),
            $labelField->children('label')->text()
        );
        self::assertSame('Cette valeur ne doit pas être vide.', $labelField->children('ul > li')->text());

        self::assertSame(
            $translator->trans('admin.unit.form.abbreviation.placeholder'),
            $abbreviationField->children('label')->text()
        );
        self::assertSame('Cette valeur ne doit pas être vide.', $abbreviationField->children('ul > li')->text());

        $units = $unitRepository->getAllUnits();
        self::assertCount(1, $units);
    }

    public function testChangeLabelFailWithUnitNotFound(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

        /** @var UnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(UnitRepository::class);
        UnitFactory::createOne(['label' => 'Kilogramme', 'abbreviation' => 'kg']);
        $units = $unitRepository->getAllUnits();
        self::assertCount(1, $units);

        // Act
        $this->client->request(Request::METHOD_GET, \sprintf(self::CHANGE_LABEL_URI, $faker->uuid()));

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $response = $this->client->getCrawler();

        $title = $response->filter('h1')->text();

        self::assertEquals('Page non trouvée', $title);
    }

    public function testCancelDuringUnitRename(): void
    {
        // Arrange
        /** @var UnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(UnitRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $unit = UnitFactory::createOne(['label' => 'Kilogramme', 'abbreviation' => 'kg']);
        $units = $unitRepository->getAllUnits();
        self::assertCount(1, $units);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_LABEL_URI, $unit->_real()->uuid())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans('admin.unit.changeLabel.titlePage', ['%unitName%' => 'Kilogramme'])
        );

        $cancelLink = $crawler->selectLink($translator->trans('cancel'));
        self::assertCount(1, $cancelLink, 'Cancel link should exist');

        $this->client->click($cancelLink->link());

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertRouteSame(GetUnitsController::ROUTE_NAME);

        /** @var Unit $unitAfterCancel */
        $unitAfterCancel = $unitRepository->getBySlug($unit->_real()->slug());
        self::assertSame($unit->_real()->label(), $unitAfterCancel->label()->toString());
        self::assertSame($unit->_real()->abbreviation(), $unitAfterCancel->abbreviation());

        $units = $unitRepository->getAllUnits();
        self::assertCount(1, $units);
    }
}
