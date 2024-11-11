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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\Tax\RevaluateTax;

use Admin\Entities\Exception\Tax\TaxAlreadyExistsException;
use Admin\Entities\Tax\Tax;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Admin\UseCases\Gateway\TaxRepository;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

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

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $tax = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $taxRepository->save($tax);
        $taxes = $taxRepository->findAllTaxes();
        self::assertCount(1, $taxes);

        // Act
        $crawler = $client->request(
            Request::METHOD_GET,
            \sprintf(self::REEVALUATE_TAX_URI, TaxDataBuilder::UUID_VALID)
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans('admin.tax.revaluate.titlePage', ['%taxName%' => $tax->name()->toString()])
        );

        $form = $crawler->selectButton($translator->trans('admin.tax.revaluate.button'))->form([
            'revaluateTax[rate]' => 10,
            'revaluateTax[uuid]' => $tax->uuid()->toString(),
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/taxes');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertSame($translator->trans('admin.tax.revaluate.success'), $flash);

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

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

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
        $crawler = $client->request(
            Request::METHOD_GET,
            \sprintf(self::REEVALUATE_TAX_URI, TaxDataBuilder::UUID_VALID)
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans('admin.tax.revaluate.titlePage', ['%taxName%' => $tax1->name()->toString()])
        );

        $form = $crawler->selectButton($translator->trans('admin.tax.revaluate.button'))->form([
            'revaluateTax[rate]' => 10.0,
            'revaluateTax[uuid]' => $tax1->uuid()->toString(),
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

        /** @var Tax $taxRevaluated */
        $taxRevaluated = $taxRepository->findById($tax1->uuid()->toString());
        self::assertSame('TVA taux normal', $taxRevaluated->name()->toString());
        self::assertSame(0.2, $taxRevaluated->rate());
    }

    public function testCreateTaxFailWithBadRequestException(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var TaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(TaxRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $tax = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $taxRepository->save($tax);

        // Act
        $crawler = $client->request(
            Request::METHOD_GET,
            \sprintf(self::REEVALUATE_TAX_URI, TaxDataBuilder::UUID_VALID)
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans(
            'admin.tax.revaluate.titlePage',
            ['%taxName%' => $tax->name()->toString()]
        ));

        $form = $crawler->selectButton($translator->trans('admin.tax.revaluate.button'))->form([
            'revaluateTax[rate]' => 120.0,
            'revaluateTax[uuid]' => $tax->uuid()->toString(),
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response = $client->getCrawler();

        $rateField = $response->filter('form')->children('div')->first();

        self::assertSame($translator->trans('admin.tax.form.rate.label'), $rateField->children('label')->text());
        self::assertSame(
            $translator->trans('tax.rate.invalid', [], 'validators'),
            $rateField->children('ul > li')->text()
        );
    }

    public function testRevaluateTaxFailWithTaxNotFound(): void
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
            \sprintf(self::REEVALUATE_TAX_URI, $faker->uuid())
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $response = $client->getCrawler();

        $title = $response->filter('h1')->text();

        self::assertEquals('Page non trouvée', $title);
    }
}
