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

namespace Admin\Tests\Adapters\controller\Symfony\Controller\Company\UpdateCompany;

use Admin\Adapters\Gateway\ORM\Repository\DoctrineCompanyRepository;
use Admin\Tests\DataBuilder\CompanyDataBuilder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group functionalTest
 */
final class UpdateCompanyControllerTest extends WebTestCase
{
    private const UPDATE_COMPANY_URI = '/admin/company/%s/update';

    public function testUpdateCompanyControllerWillSucceed(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);
        $company = (new CompanyDataBuilder())->create('Dev-Int Création')->build();
        $companyRepository->save($company);

        $companyCreated = $companyRepository->findByName('Dev-Int Création');
        self::assertSame('5, rue des Plantes', $companyCreated->address()->address());
        self::assertSame('75000', $companyCreated->address()->postalCode());
        self::assertSame('Paris', $companyCreated->address()->town());

        // Act
        $crawler = $client->request(
            Request::METHOD_GET,
            sprintf(self::UPDATE_COMPANY_URI, $company->slug())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Update Company');

        $form = $crawler->selectButton('Update')->form([
            'updateCompany[address]' => '12, rue des Singes',
            'updateCompany[postalCode]' => '56000',
            'updateCompany[town]' => 'Vannes',
            'updateCompany[country]' => 'France',
            'updateCompany[phone]' => '+33297000000',
            'updateCompany[email]' => 'test@test.fr',
            'updateCompany[contact]' => 'Laurent',
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/company/');

        $companyCreated = $companyRepository->findByName('Dev-Int Création');
        self::assertSame('12, rue des Singes', $companyCreated->address()->address());
        self::assertSame('56000', $companyCreated->address()->postalCode());
        self::assertSame('Vannes', $companyCreated->address()->town());
    }

    public function testUpdateCompanyControllerWillFailWithCompanyNotFound(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);
        $company = (new CompanyDataBuilder())->create('Dev-Int Création')->build();
        $companyRepository->save($company);

        // Act
        $client->request(
            Request::METHOD_GET,
            sprintf(self::UPDATE_COMPANY_URI, 'Test company')
        );

        // Assert
        self::assertResponseStatusCodeSame(
            Response::HTTP_NOT_FOUND,
            '\'Admin\Adapters\Gateway\ORM\Entity\Company\' object not found by \'Symfony\Bridge\Doctrine\ArgumentResolver\EntityValueResolver\'. (404 Not Found)'
        );
    }
}
