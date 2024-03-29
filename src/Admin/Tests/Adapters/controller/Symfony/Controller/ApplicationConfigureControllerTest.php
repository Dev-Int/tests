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

namespace Admin\Tests\Adapters\controller\Symfony\Controller;

use Admin\Adapters\Gateway\ORM\Repository\DoctrineCompanyRepository;
use Admin\Tests\DataBuilder\CompanyDataBuilder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class ApplicationConfigureControllerTest extends WebTestCase
{
    private const APPLICATION_CONFIGURE_URI = '/admin/configure/application';

    public function testApplicationConfigurePageWillSucceed(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);

        $company = (new CompanyDataBuilder())->create('TestCompany')->build();
        $companyRepository->save($company);

        // Act
        $client->request(Request::METHOD_GET, self::APPLICATION_CONFIGURE_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Configure application');
    }

    public function testApplicationConfigurePageRedirectConfigurePage(): void
    {
        // Arrange
        $client = self::createClient();

        // Act
        $client->request(Request::METHOD_GET, self::APPLICATION_CONFIGURE_URI);

        // Assert
        self::assertResponseRedirects('/admin/configure');
    }
}
