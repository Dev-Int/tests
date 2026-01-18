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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\Unit\CreateUnit;

use Admin\Entities\Exception\Company\NoCompanyRegistered;
use Admin\Entities\Repository\UnitRepository;
use Admin\Entities\Unit\Unit;
use Admin\Tests\Factory\CompanyFactory;
use Admin\Tests\Factory\UnitFactory;
use Shared\Tests\BaseFunctionalTestCase;
use Shared\Tests\RedirectsToLoginTestTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 */
final class CreateUnitControllerTest extends BaseFunctionalTestCase
{
    use Factories;
    use RedirectsToLoginTestTrait;

    private const string CREATE_UNIT_URI = '/admin/units/create';

    public function testCreateUnitWillSucceed(): void
    {
        // Arrange
        /** @var UnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(UnitRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        CompanyFactory::createOne(['name' => 'Test company']);

        // Act
        $crawler = $this->client->request(Request::METHOD_POST, self::CREATE_UNIT_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.unit.create.titlePage'));

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createUnit[label]' => 'Kilogramme',
            'createUnit[abbreviation]' => 'kg',
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/units');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertSame($translator->trans('admin.unit.create.success'), $flash);

        /** @var Unit $unitCreated */
        $unitCreated = $unitRepository->getBySlug('kilogramme');
        self::assertSame('Kilogramme', $unitCreated->label()->toString());
        self::assertSame('kilogramme', $unitCreated->slug());
        self::assertSame('kg', $unitCreated->abbreviation());
    }

    public function testCreateUnitFailWithAlreadyExistsException(): void
    {
        // Arrange
        /** @var UnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(UnitRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        CompanyFactory::createOne(['name' => 'Test company']);
        UnitFactory::createOne(['label' => 'Kilogramme', 'abbreviation' => 'kg']);

        // Act
        $crawler = $this->client->request(Request::METHOD_POST, self::CREATE_UNIT_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.unit.create.titlePage'));

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createUnit[label]' => 'Kilogramme',
            'createUnit[abbreviation]' => 'kg',
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/units');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertSame('Unit already exists.', $flash);

        /** @var Unit $unitCreated */
        $unitCreated = $unitRepository->getBySlug('kilogramme');
        self::assertSame('Kilogramme', $unitCreated->label()->toString());
        self::assertSame('kg', $unitCreated->abbreviation());
    }

    public function testCreateUnitFailWithBadRequestException(): void
    {
        // Arrange
        /** @var UnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(UnitRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        CompanyFactory::createOne(['name' => 'Test company']);
        UnitFactory::createOne(['label' => 'Kilogramme', 'abbreviation' => 'kg']);

        // Act
        $crawler = $this->client->request(Request::METHOD_POST, self::CREATE_UNIT_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.unit.create.titlePage'));

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createUnit[label]' => '',
            'createUnit[abbreviation]' => '',
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response = $this->client->getCrawler();

        $labelField = $response->filter('form')->children('div')->first();
        $abbreviationField = $labelField->siblings();

        self::assertSame('Intitulé de l\'unité', $labelField->children('label')->text());
        self::assertSame('Cette valeur ne doit pas être vide.', $labelField->children('ul > li')->text());

        self::assertSame('Abréviation de l\'unité', $abbreviationField->children('label')->text());
        self::assertSame('Cette valeur ne doit pas être vide.', $abbreviationField->children('ul > li')->text());

        /** @var Unit $unitCreated */
        $unitCreated = $unitRepository->getBySlug('kilogramme');
        self::assertSame('Kilogramme', $unitCreated->label()->toString());
        self::assertSame('kg', $unitCreated->abbreviation());
    }

    public function testCreateUnitFailWithNoCompanyRegisteredException(): void
    {
        // Arrange
        UnitFactory::createOne(['label' => 'Kilogramme', 'abbreviation' => 'kg']);

        // Act
        $this->client->request(Request::METHOD_POST, self::CREATE_UNIT_URI);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/configure');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertSame(NoCompanyRegistered::MESSAGE, $flash);
    }

    protected function getProtectedUri(): string
    {
        return self::CREATE_UNIT_URI;
    }
}
