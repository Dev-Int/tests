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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\Company\UpdateCompany;

use Admin\Entities\Repository\CompanyRepository;
use Admin\Tests\Factory\CompanyFactory;
use App\Shared\Tests\BaseFunctionalTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 */
final class UpdateCompanyControllerTest extends BaseFunctionalTestCase
{
    use Factories;

    private const UPDATE_COMPANY_URI = '/admin/company/%s/update';

    public function testUpdateCompanyControllerWillSucceed(): void
    {
        // Arrange
        /** @var CompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(CompanyRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $company = CompanyFactory::createOne([
            'name' => 'Dev-Int Création',
            'address' => '5, rue des Plantes',
            'postalCode' => '75000',
            'city' => 'Paris',
        ]);

        $companyCreated = $companyRepository->getByName('Dev-Int Création');
        self::assertSame('5, rue des Plantes', $companyCreated->address()->address());
        self::assertSame('75000', $companyCreated->address()->postalCode());
        self::assertSame('Paris', $companyCreated->address()->city());

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::UPDATE_COMPANY_URI, $company->slug())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans('admin.company.update.titlePage', ['%companyName%' => 'Dev-Int Création'])
        );

        $form = $crawler->selectButton($translator->trans('admin.company.update.button'))->form([
            'updateCompany[address]' => '12, rue des Singes',
            'updateCompany[postalCode]' => '56000',
            'updateCompany[city]' => 'Vannes',
            'updateCompany[country]' => 'France',
            'updateCompany[phone]' => '+33297000000',
            'updateCompany[email]' => 'test@test.fr',
            'updateCompany[contact]' => 'Laurent',
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/company');

        $companyCreated = $companyRepository->getByName('Dev-Int Création');
        self::assertSame('12, rue des Singes', $companyCreated->address()->address());
        self::assertSame('56000', $companyCreated->address()->postalCode());
        self::assertSame('Vannes', $companyCreated->address()->city());
    }

    public function testUpdateCompanyControllerWillFailWithCompanyNotFound(): void
    {
        // Arrange
        CompanyFactory::createOne(['name' => 'Dev-Int Création']);

        // Act
        $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::UPDATE_COMPANY_URI, 'Test company')
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $response = $this->client->getCrawler();

        $title = $response->filter('h1')->text();

        self::assertEquals('Page non trouvée', $title);
    }
}
