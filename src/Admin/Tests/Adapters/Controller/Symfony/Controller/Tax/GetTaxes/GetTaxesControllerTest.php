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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\Tax\GetTaxes;

use Admin\Entities\Exception\Tax\NoTaxRegisteredException;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Admin\UseCases\Gateway\TaxRepository;
use App\Shared\Tests\BaseFunctionalTestCase;
use Faker\Factory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group functionalTest
 */
final class GetTaxesControllerTest extends BaseFunctionalTestCase
{
    private const GET_TAXES_URI = '/admin/taxes';

    public function testGetTaxesWillSucceed(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

        /** @var TaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(TaxRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $taxBuilder = new TaxDataBuilder();
        $tax1 = $taxBuilder->create('TVA taux normal', 20.0)->build();
        $tax2 = $taxBuilder->create('TVA taux réduit', 5.5)
            ->withUuid($faker->uuid())
            ->build()
        ;
        $taxRepository->save($tax1);
        $taxRepository->save($tax2);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::GET_TAXES_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.tax.titlePage'));

        $list = $crawler->filter('body > div.container > main > article > ul.table > turbo-frame')
            ->children('li.li-unstyled')
        ;
        self::assertCount(2, $list);
    }

    public function testGetTaxesFailWithNoTaxRegisteredException(): void
    {
        // Arrange && Act
        $this->client->request(Request::METHOD_GET, self::GET_TAXES_URI);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/configure');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertSame(NoTaxRegisteredException::MESSAGE, $flash);
    }
}
