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

namespace Admin\Tests\Adapters\controller\Symfony\Controller\Company\CreateCompany;

use Admin\Adapters\Gateway\ORM\Repository\DoctrineCompanyRepository;
use Admin\Entities\Exception\CompanyAlreadyExistsException;
use Admin\Tests\DataBuilder\CompanyDataBuilder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group functionalTest
 */
final class CreateCompanyControllerTest extends WebTestCase
{
    private const CREATE_COMPANY_URI = '/admin/company/create';

    public function testCreateCompanyControllerWillSucceed(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);

        // Act
        $crawler = $client->request(Request::METHOD_GET, self::CREATE_COMPANY_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Create Company');

        $form = $crawler->selectButton('Create')->form([
            'createCompany[name]' => 'Dev-Int Création',
            'createCompany[address]' => '5, rue des Plantes',
            'createCompany[postalCode]' => '75000',
            'createCompany[town]' => 'Paris',
            'createCompany[country]' => 'France',
            'createCompany[phone]' => '+33297000000',
            'createCompany[email]' => 'test@test.fr',
            'createCompany[contact]' => 'Laurent',
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/');

        $companyCreated = $companyRepository->findByName('Dev-Int Création');
        self::assertSame('dev-int-creation', $companyCreated->slug());

        // The configuration only begin. The admin page is redirected throw admin configure.
        $client->followRedirect(); // Admin page
        $admin = $client->followRedirect(); // Configure page
        $flash = $admin->filter('body > div.container')->children('div.flash.flash-success')->text();

        self::assertEquals('Company created', $flash);
    }

    public function testCreateCompanyControllerWillThrowAlreadyExistsException(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);

        $company = (new CompanyDataBuilder())->create('TestCompany')->build();
        $companyRepository->save($company);

        // Act
        $crawler = $client->request(Request::METHOD_GET, self::CREATE_COMPANY_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Create Company');

        $form = $crawler->selectButton('Create')->form([
            'createCompany[name]' => 'Dev-Int Création',
            'createCompany[address]' => '5, rue des Plantes',
            'createCompany[postalCode]' => '75000',
            'createCompany[town]' => 'Paris',
            'createCompany[country]' => 'France',
            'createCompany[phone]' => '+33297000000',
            'createCompany[email]' => 'test@test.fr',
            'createCompany[contact]' => 'Laurent',
        ]);

        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/');

        // The configuration only begin. The admin page is redirected throw admin configure.
        $client->followRedirect(); // Admin page
        $admin = $client->followRedirect(); // Configure page
        $flash = $admin->filter('body > div.container')->children('div.flash.flash-error')->text();

        self::assertEquals(CompanyAlreadyExistsException::MESSAGE, $flash);
    }
}
