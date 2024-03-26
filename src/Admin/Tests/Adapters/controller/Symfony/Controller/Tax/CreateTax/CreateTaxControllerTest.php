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

namespace Admin\Tests\Adapters\controller\Symfony\Controller\Tax\CreateTax;

use Admin\Adapters\Gateway\ORM\Entity\Tax;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineTaxRepository;
use Admin\Entities\Exception\TaxAlreadyExistsException;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CreateTaxControllerTest extends WebTestCase
{
    private const CREATE_TAX_URI = '/admin/taxes/create';

    public function testCreateTaxWillSucceed(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineTaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(DoctrineTaxRepository::class);

        // Act
        $crawler = $client->request(Request::METHOD_GET, self::CREATE_TAX_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Create Tax');

        $form = $crawler->selectButton('Create')->form([
            'createTax[name]' => 'TVA taux normal',
            'createTax[rate]' => 20.0,
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/taxes');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container')->children('div.flash.flash-success')->text();

        self::assertSame('Tax created', $flash);

        /** @var Tax $taxCreated */
        $taxCreated = $taxRepository->findOneBy(['name' => 'TVA taux normal']);
        self::assertSame('TVA taux normal', $taxCreated->name());
        self::assertSame(0.2, $taxCreated->rate());
    }

    public function testCreateTaxFailWithAlreadyExistsException(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineTaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(DoctrineTaxRepository::class);
        $tax = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $taxRepository->save($tax);

        // Act
        $crawler = $client->request(Request::METHOD_GET, self::CREATE_TAX_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Create Tax');

        $form = $crawler->selectButton('Create')->form([
            'createTax[name]' => 'TVA taux normal',
            'createTax[rate]' => 20.0,
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/taxes');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container')->children('div.flash.flash-error')->text();

        self::assertSame(TaxAlreadyExistsException::MESSAGE, $flash);

        /** @var Tax $taxCreated */
        $taxCreated = $taxRepository->findOneBy(['name' => 'TVA taux normal']);
        self::assertSame('TVA taux normal', $taxCreated->name());
        self::assertSame(0.2, $taxCreated->rate());
    }

    public function testCreateTaxFailWithBadRequestException(): void
    {
        // Arrange
        $client = self::createClient();

        // Act
        $crawler = $client->request(Request::METHOD_GET, self::CREATE_TAX_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Create Tax');

        $form = $crawler->selectButton('Create')->form([
            'createTax[name]' => '',
            'createTax[rate]' => 0.0,
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response = $client->getCrawler();

        $nameField = $response->filter('form')->children('div')->first();

        self::assertSame('Nom de la taxe', $nameField->children('label')->text());
        self::assertSame('This value should not be blank.', $nameField->children('ul > li')->text());
    }
}
