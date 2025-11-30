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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\Tax\CreateTax;

use Admin\Adapters\Gateway\ORM\Entity\Tax;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineCompanyRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineTaxRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineUnitRepository;
use Admin\Entities\Exception\Tax\TaxAlreadyExistsException;
use Admin\Entities\Exception\Unit\NoUnitRegisteredException;
use Admin\Tests\DataBuilder\CompanyDataBuilder;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use App\Shared\Tests\BaseFunctionalTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group functionalTest
 */
final class CreateTaxControllerTest extends BaseFunctionalTestCase
{
    private const CREATE_TAX_URI = '/admin/taxes/create';

    public function testCreateTaxWillSucceed(): void
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

        /** @var DoctrineTaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(DoctrineTaxRepository::class);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_TAX_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.tax.create.titlePage'));

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createTax[name]' => 'TVA taux normal',
            'createTax[rate]' => 20.0,
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/taxes');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertSame($translator->trans('admin.tax.create.success'), $flash);

        /** @var Tax $taxCreated */
        $taxCreated = $taxRepository->findOneBy(['name' => 'TVA taux normal']);
        self::assertSame('TVA taux normal', $taxCreated->name());
        self::assertSame(0.2, $taxCreated->rate());
    }

    public function testCreateTaxFailWithAlreadyExistsException(): void
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

        /** @var DoctrineTaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(DoctrineTaxRepository::class);
        $tax = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $taxRepository->save($tax);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_TAX_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.tax.create.titlePage'));

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createTax[name]' => 'TVA taux normal',
            'createTax[rate]' => 20.0,
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/taxes');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertSame(TaxAlreadyExistsException::MESSAGE, $flash);

        /** @var Tax $taxCreated */
        $taxCreated = $taxRepository->findOneBy(['name' => 'TVA taux normal']);
        self::assertSame('TVA taux normal', $taxCreated->name());
        self::assertSame(0.2, $taxCreated->rate());
    }

    public function testCreateTaxFailWithBadRequestException(): void
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
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_TAX_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.tax.create.titlePage'));

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createTax[name]' => '',
            'createTax[rate]' => 0.0,
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response = $this->client->getCrawler();

        $nameField = $response->filter('form')->children('div')->first();

        self::assertSame($translator->trans('admin.tax.form.name.label'), $nameField->children('label')->text());
        self::assertSame('Cette valeur ne doit pas être vide.', $nameField->children('ul > li')->text());
    }

    public function testCreateTaxFailWithRateTooLargeException(): void
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
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_TAX_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.tax.create.titlePage'));

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createTax[name]' => 'TVA taux normal',
            'createTax[rate]' => 120.0,
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response = $this->client->getCrawler();

        $nameField = $response->filter('form')->children('div')->first();
        $rateField = $nameField->siblings();

        self::assertSame($translator->trans('admin.tax.form.rate.label'), $rateField->children('label')->text());
        self::assertSame(
            $translator->trans('tax.rate.invalid', [], 'validators'),
            $rateField->children('ul > li')->text()
        );
    }

    public function testCreateUnitFailWithNoCompanyRegisteredException(): void
    {
        // Arrange
        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);
        $company = (new CompanyDataBuilder())->create('Test company')->build();
        $companyRepository->save($company);

        // Act
        $this->client->request(Request::METHOD_POST, self::CREATE_TAX_URI);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/configure');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertSame(NoUnitRegisteredException::MESSAGE, $flash);
    }
}
