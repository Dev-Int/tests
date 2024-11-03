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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\Tax\RenameTax;

use Admin\Entities\Exception\Tax\TaxAlreadyExistsException;
use Admin\Entities\Tax\Tax;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Admin\UseCases\Gateway\TaxRepository;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group functionalTest
 */
final class RenameTaxControllerTest extends WebTestCase
{
    private const RENAME_TAX_URI = '/admin/taxes/%s/rename';

    public function testRenameTaxWillSucceed(): void
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
        $crawler = $client->request(
            Request::METHOD_GET,
            \sprintf(self::RENAME_TAX_URI, TaxDataBuilder::UUID_VALID)
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Rename Tax');

        $form = $crawler->selectButton('Rename')->form([
            'renameTax[name]' => 'TVA taux réduit',
            'renameTax[uuid]' => $tax->uuid()->toString(),
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/taxes');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertSame('Tax renamed', $flash);

        $taxes = $taxRepository->findAllTaxes();
        self::assertCount(1, $taxes);

        /** @var Tax $taxRenamed */
        $taxRenamed = $taxRepository->findById($tax->uuid()->toString());
        self::assertSame('TVA taux réduit', $taxRenamed->name()->toString());
        self::assertSame(0.2, $taxRenamed->rate());
    }

    public function testRenameTaxFailWithAlreadyExistsException(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var TaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(TaxRepository::class);
        $tax1 = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $tax2 = (new TaxDataBuilder())->create('TVA taux réduit', 20.0)
            ->withUuid('2fd3cd27-c9e8-49e2-b993-48390d3c665a')
            ->build()
        ;
        $taxRepository->save($tax1);
        $taxRepository->save($tax2);
        $taxes = $taxRepository->findAllTaxes();
        self::assertCount(2, $taxes);

        // Act
        $crawler = $client->request(
            Request::METHOD_GET,
            \sprintf(self::RENAME_TAX_URI, TaxDataBuilder::UUID_VALID)
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Rename Tax');

        $form = $crawler->selectButton('Rename')->form([
            'renameTax[name]' => 'TVA taux réduit',
            'renameTax[uuid]' => $tax1->uuid()->toString(),
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/taxes');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertSame(TaxAlreadyExistsException::MESSAGE, $flash);

        $taxes = $taxRepository->findAllTaxes();
        self::assertCount(2, $taxes);

        /** @var Tax $taxRenamed */
        $taxRenamed = $taxRepository->findById($tax1->uuid()->toString());
        self::assertSame('TVA taux normal', $taxRenamed->name()->toString());
        self::assertSame(0.2, $taxRenamed->rate());
    }

    public function testRenameTaxTaxNotFound(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $client = self::createClient();

        /** @var TaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(TaxRepository::class);
        $tax = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $taxRepository->save($tax);
        $taxes = $taxRepository->findAllTaxes();
        self::assertCount(1, $taxes);

        // Act
        $client->request(
            Request::METHOD_GET,
            \sprintf(self::RENAME_TAX_URI, $faker->uuid())
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $response = $client->getCrawler();

        $title = $response->filter('h1')->text();

        self::assertEquals('Page non trouvée', $title);
    }
}
