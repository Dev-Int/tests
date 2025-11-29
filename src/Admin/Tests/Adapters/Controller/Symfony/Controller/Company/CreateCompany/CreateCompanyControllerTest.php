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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\Company\CreateCompany;

use Admin\Adapters\Gateway\ORM\Repository\DoctrineCompanyRepository;
use Admin\Entities\Exception\Company\CompanyAlreadyExistsException;
use Admin\Tests\DataBuilder\CompanyDataBuilder;
use App\Shared\Tests\BaseFunctionalTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group functionalTest
 */
final class CreateCompanyControllerTest extends BaseFunctionalTestCase
{
    private const CREATE_COMPANY_URI = '/admin/company/create';

    public function testCreateCompanyControllerWillSucceed(): void
    {
        // Arrange
        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_COMPANY_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.company.create.titlePage'));

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createCompany[name]' => 'Dev-Int Création',
            'createCompany[address]' => '5, rue des Plantes',
            'createCompany[postalCode]' => '75000',
            'createCompany[city]' => 'Paris',
            'createCompany[country]' => 'France',
            'createCompany[phone]' => '+33297000000',
            'createCompany[email]' => 'test@test.fr',
            'createCompany[contact]' => 'Laurent',
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/');

        $companyCreated = $companyRepository->findByName('Dev-Int Création');
        self::assertSame('dev-int-creation', $companyCreated->slug());

        // The configuration only begin. The admin page is redirected throw admin configure.
        $this->client->followRedirect(); // Admin page
        $admin = $this->client->followRedirect(); // Configure page
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertEquals($translator->trans('admin.company.create.success'), $flash);
    }

    public function testCreateCompanyControllerWillThrowAlreadyExistsException(): void
    {
        // Arrange
        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $company = (new CompanyDataBuilder())->create('TestCompany')->build();
        $companyRepository->save($company);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_COMPANY_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.company.create.titlePage'));

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createCompany[name]' => 'Dev-Int Création',
            'createCompany[address]' => '5, rue des Plantes',
            'createCompany[postalCode]' => '75000',
            'createCompany[city]' => 'Paris',
            'createCompany[country]' => 'France',
            'createCompany[phone]' => '+33297000000',
            'createCompany[email]' => 'test@test.fr',
            'createCompany[contact]' => 'Laurent',
        ]);

        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/');

        // The configuration only begin. The admin page is redirected throw admin configure.
        $this->client->followRedirect(); // Admin page
        $admin = $this->client->followRedirect(); // Configure page
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertEquals(CompanyAlreadyExistsException::MESSAGE, $flash);
    }

    public function testCreateCompanyControllerWillThrowBadRequestException(): void
    {
        // Arrange
        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $company = (new CompanyDataBuilder())->create('TestCompany')->build();
        $companyRepository->save($company);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_COMPANY_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.company.create.titlePage'));

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createCompany[name]' => 'Dev-Int Création',
            'createCompany[address]' => '5, rue des Plantes',
            'createCompany[postalCode]' => '75000',
            'createCompany[city]' => 'Paris',
            'createCompany[country]' => 'France',
            'createCompany[phone]' => '02.97-00 000',
            'createCompany[email]' => 'test@test.fr',
            'createCompany[contact]' => 'Laurent',
        ]);

        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response = $this->client->getCrawler();

        $groupField = $response->filter('form')->children('div')->eq(4);
        $phoneField = $groupField->children('div')->first();

        self::assertSame($translator->trans('phone'), $phoneField->children('label')->text());
        self::assertSame('Cette valeur n\'est pas valide.', $phoneField->children('ul > li')->text());
    }
}
