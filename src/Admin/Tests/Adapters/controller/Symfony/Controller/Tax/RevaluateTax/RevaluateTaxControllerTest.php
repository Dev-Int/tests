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

namespace Admin\Tests\Adapters\controller\Symfony\Controller\Tax\RevaluateTax;

use Admin\Entities\Exception\TaxAlreadyExistsException;
use Admin\Entities\Tax\Tax;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Admin\UseCases\Gateway\TaxRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group functionalTest
 */
final class RevaluateTaxControllerTest extends WebTestCase
{
    private const REEVALUATE_TAX_URI = '/admin/taxes/%s/revaluate';

    public function testRevaluateTaxWillSucceed(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var TaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(TaxRepository::class);
        $tax = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $taxRepository->save($tax);
        $taxes = $taxRepository->findAllTaxes();
        self::assertCount(1, $taxes);

        // Act
        $crawler = $client->request(Request::METHOD_GET, sprintf(self::REEVALUATE_TAX_URI, TaxDataBuilder::UUID_VALID));

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Revaluate Tax');

        $form = $crawler->selectButton('Revaluate')->form([
            'revaluateTax[rate]' => 10,
            'revaluateTax[uuid]' => $tax->uuid()->toString(),
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/taxes');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container')->children('div.flash.flash-success')->text();

        self::assertSame('Tax revaluated', $flash);

        $taxes = $taxRepository->findAllTaxes();
        self::assertCount(1, $taxes);

        /** @var Tax $taxRenamed */
        $taxRenamed = $taxRepository->findById($tax->uuid()->toString());
        self::assertSame('TVA taux normal', $taxRenamed->name()->toString());
        self::assertSame(0.1, $taxRenamed->rate());
    }

    public function testRevaluateTaxFailWithAlreadyExistsException(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var TaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(TaxRepository::class);
        $tax1 = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $tax2 = (new TaxDataBuilder())->create('TVA taux normal', 10.0)
            ->withUuid('2fd3cd27-c9e8-49e2-b993-48390d3c665a')
            ->build()
        ;
        $taxRepository->save($tax1);
        $taxRepository->save($tax2);
        $taxes = $taxRepository->findAllTaxes();
        self::assertCount(2, $taxes);

        // Act
        $crawler = $client->request(Request::METHOD_GET, sprintf(self::REEVALUATE_TAX_URI, TaxDataBuilder::UUID_VALID));

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Revaluate Tax');

        $form = $crawler->selectButton('Revaluate')->form([
            'revaluateTax[rate]' => 10,
            'revaluateTax[uuid]' => $tax1->uuid()->toString(),
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/taxes');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container')->children('div.flash.flash-error')->text();

        self::assertSame(TaxAlreadyExistsException::MESSAGE, $flash);

        $taxes = $taxRepository->findAllTaxes();
        self::assertCount(2, $taxes);

        /** @var Tax $taxRevaluated */
        $taxRevaluated = $taxRepository->findById($tax1->uuid()->toString());
        self::assertSame('TVA taux normal', $taxRevaluated->name()->toString());
        self::assertSame(0.2, $taxRevaluated->rate());
    }
}
