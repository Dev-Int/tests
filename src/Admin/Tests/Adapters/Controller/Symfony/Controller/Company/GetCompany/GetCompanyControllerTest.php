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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\Company\GetCompany;

use Admin\Adapters\Gateway\ORM\Repository\DoctrineCompanyRepository;
use Admin\Entities\Exception\Company\NoCompanyRegisteredException;
use Admin\Tests\DataBuilder\CompanyDataBuilder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group functionalTest
 */
final class GetCompanyControllerTest extends WebTestCase
{
    private const GET_COMPANY_URI = '/admin/company';

    public function testGetCompanyWillSucceed(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $company = (new CompanyDataBuilder())->create('Test Company')->build();
        $companyRepository->save($company);

        // Act
        $crawler = $client->request(Request::METHOD_GET, self::GET_COMPANY_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.company.titlePage'));

        $firstLine = $crawler
            ->filter('body > div.container > main > article > table > tbody > tr')
            ->children('td')
        ;
        self::assertSame($translator->trans('name'), $firstLine->first()->text());
        self::assertSame('Test Company', $firstLine->siblings()->text());
    }

    public function testGetCompanyFailWithNoCompanyRegisteredException(): void
    {
        // Arrange
        $client = self::createClient();

        // Act
        $client->request(Request::METHOD_GET, self::GET_COMPANY_URI);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/configure');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertSame(NoCompanyRegisteredException::MESSAGE, $flash);
    }
}
