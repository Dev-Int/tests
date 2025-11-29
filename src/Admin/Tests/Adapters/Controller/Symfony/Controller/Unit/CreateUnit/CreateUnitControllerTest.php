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

use Admin\Adapters\Gateway\ORM\Entity\Unit;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineCompanyRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineUnitRepository;
use Admin\Entities\Exception\Company\NoCompanyRegisteredException;
use Admin\Tests\DataBuilder\CompanyDataBuilder;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use App\Shared\Tests\BaseFunctionalTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group functionalTest
 */
final class CreateUnitControllerTest extends BaseFunctionalTestCase
{
    private const CREATE_UNIT_URI = '/admin/units/create';

    public function testCreateUnitWillSucceed(): void
    {
        // Arrange
        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $company = (new CompanyDataBuilder())->create('Test company')->build();
        $companyRepository->save($company);

        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(DoctrineUnitRepository::class);

        // Act
        $crawler = $this->client->request(Request::METHOD_POST, self::CREATE_UNIT_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.unit.create.titlePage'));

        $form = $crawler->selectButton('Create')->form([
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
        $unitCreated = $unitRepository->findOneBy(['slug' => 'kilogramme']);
        self::assertSame('Kilogramme', $unitCreated->label());
        self::assertSame('kilogramme', $unitCreated->slug());
        self::assertSame('kg', $unitCreated->abbreviation());
    }

    public function testCreateUnitFailWithAlreadyExistsException(): void
    {
        // Arrange
        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $company = (new CompanyDataBuilder())->create('Test company')->build();
        $companyRepository->save($company);

        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(DoctrineUnitRepository::class);
        $unit = (new UnitDataBuilder())->create('Kilogramme', 'kg')->build();
        $unitRepository->save($unit);

        // Act
        $crawler = $this->client->request(Request::METHOD_POST, self::CREATE_UNIT_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.unit.create.titlePage'));

        $form = $crawler->selectButton('Create')->form([
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
        $unitCreated = $unitRepository->findOneBy(['slug' => 'kilogramme']);
        self::assertSame('Kilogramme', $unitCreated->label());
        self::assertSame('kg', $unitCreated->abbreviation());
    }

    public function testCreateUnitFailWithBadRequestException(): void
    {
        // Arrange
        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $company = (new CompanyDataBuilder())->create('Test company')->build();
        $companyRepository->save($company);

        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(DoctrineUnitRepository::class);
        $unit = (new UnitDataBuilder())->create('Kilogramme', 'kg')->build();
        $unitRepository->save($unit);

        // Act
        $crawler = $this->client->request(Request::METHOD_POST, self::CREATE_UNIT_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.unit.create.titlePage'));

        $form = $crawler->selectButton('Create')->form([
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
        $unitCreated = $unitRepository->findOneBy(['slug' => 'kilogramme']);
        self::assertSame('Kilogramme', $unitCreated->label());
        self::assertSame('kg', $unitCreated->abbreviation());
    }

    public function testCreateUnitFailWithNoCompanyRegisteredException(): void
    {
        // Arrange
        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(DoctrineUnitRepository::class);
        $unit = (new UnitDataBuilder())->create('Kilogramme', 'kg')->build();
        $unitRepository->save($unit);

        // Act
        $this->client->request(Request::METHOD_POST, self::CREATE_UNIT_URI);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/configure');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertSame(NoCompanyRegisteredException::MESSAGE, $flash);
    }
}
