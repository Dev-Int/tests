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

namespace Admin\Tests\Adapters\controller\Symfony\Controller\Tax\GetTaxes;

use Admin\Adapters\Gateway\ORM\Repository\DoctrineTaxRepository;
use Admin\Entities\Exception\NoTaxRegisteredException;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group functionalTest
 */
final class GetTaxesControllerTest extends WebTestCase
{
    private const GET_TAXES_URI = '/admin/taxes';

    public function testGetTaxesWillSucceed(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineTaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(DoctrineTaxRepository::class);
        $taxBuilder = new TaxDataBuilder();
        $tax1 = $taxBuilder->create('TVA taux normal', 20.0)->build();
        $tax2 = $taxBuilder->create('TVA taux réduit', 5.5)
            ->withUuid('d1e33989-ebba-4a4f-a897-13696e1a347c')
            ->build()
        ;
        $taxRepository->save($tax1);
        $taxRepository->save($tax2);

        // Act
        $crawler = $client->request(Request::METHOD_GET, self::GET_TAXES_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Taxes');

        $list = $crawler->filter('body > div.container > main > article > ul.table > turbo-frame')
            ->children('li.li-unstyled')
        ;
        self::assertCount(2, $list);
    }

    public function testGetTaxesFailWithNoTaxRegisteredException(): void
    {
        // Arrange
        $client = self::createClient();

        // Act
        $client->request(Request::METHOD_GET, self::GET_TAXES_URI);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/configure');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertSame(NoTaxRegisteredException::MESSAGE, $flash);
    }
}
