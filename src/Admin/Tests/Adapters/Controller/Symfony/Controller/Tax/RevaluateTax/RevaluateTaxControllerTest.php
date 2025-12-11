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

use Admin\Adapters\Controller\Symfony\Controller\Tax\GetTaxes\GetTaxesController;
use Admin\Entities\Exception\Tax\TaxAlreadyExists;
use Admin\Entities\Repository\TaxRepository;
use Admin\Entities\Tax\Tax;
use Admin\Tests\Factory\TaxFactory;
use App\Shared\Tests\BaseFunctionalTestCase;
use Faker\Factory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 */
final class RevaluateTaxControllerTest extends BaseFunctionalTestCase
{
    use Factories;

    private const REEVALUATE_TAX_URI = '/admin/taxes/%s/revaluate';

    public function testRevaluateTaxWillSucceed(): void
    {
        // Arrange
        /** @var TaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(TaxRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $tax = TaxFactory::createOne(['name' => 'TVA taux normal', 'rate' => 20.0]);
        $taxes = $taxRepository->getAllTaxes();
        self::assertCount(1, $taxes);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::REEVALUATE_TAX_URI, $tax->_real()->uuid())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans('admin.tax.revaluate.titlePage', ['%taxName%' => $tax->_real()->name()])
        );

        $form = $crawler->selectButton($translator->trans('admin.tax.revaluate.button'))->form([
            'revaluateTax[rate]' => 10,
            'revaluateTax[uuid]' => $tax->_real()->uuid(),
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/taxes');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertSame($translator->trans('admin.tax.revaluate.success'), $flash);

        $taxes = $taxRepository->getAllTaxes();
        self::assertCount(1, $taxes);

        /** @var Tax $taxRenamed */
        $taxRenamed = $taxRepository->getById($tax->_real()->uuid());
        self::assertSame('TVA taux normal', $taxRenamed->name()->toString());
        self::assertSame(0.1, $taxRenamed->rate());
    }

    public function testRevaluateTaxFailWithAlreadyExistsException(): void
    {
        // Arrange
        /** @var TaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(TaxRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $tax1 = TaxFactory::createOne(['name' => 'TVA taux normal', 'rate' => 20.0]);
        TaxFactory::createOne(['name' => 'TVA taux normal', 'rate' => 10.0]);
        $taxes = $taxRepository->getAllTaxes();
        self::assertCount(2, $taxes);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::REEVALUATE_TAX_URI, $tax1->_real()->uuid())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans('admin.tax.revaluate.titlePage', ['%taxName%' => $tax1->_real()->name()])
        );

        $form = $crawler->selectButton($translator->trans('admin.tax.revaluate.button'))->form([
            'revaluateTax[rate]' => 10.0,
            'revaluateTax[uuid]' => $tax1->_real()->uuid(),
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/taxes');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertSame(TaxAlreadyExists::MESSAGE, $flash);

        $taxes = $taxRepository->getAllTaxes();
        self::assertCount(2, $taxes);

        /** @var Tax $taxRevaluated */
        $taxRevaluated = $taxRepository->getById($tax1->_real()->uuid());
        self::assertSame('TVA taux normal', $taxRevaluated->name()->toString());
        self::assertSame(0.2, $taxRevaluated->rate());
    }

    public function testCreateTaxFailWithBadRequestException(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $tax = TaxFactory::createOne(['name' => 'TVA taux normal', 'rate' => 20.0]);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::REEVALUATE_TAX_URI, $tax->_real()->uuid())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans(
            'admin.tax.revaluate.titlePage',
            ['%taxName%' => $tax->_real()->name()]
        ));

        $form = $crawler->selectButton($translator->trans('admin.tax.revaluate.button'))->form([
            'revaluateTax[rate]' => 120.0,
            'revaluateTax[uuid]' => $tax->_real()->uuid(),
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response = $this->client->getCrawler();

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

        /** @var TaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(TaxRepository::class);

        TaxFactory::createOne(['name' => 'TVA taux normal', 'rate' => 20.0]);
        $taxes = $taxRepository->getAllTaxes();
        self::assertCount(1, $taxes);

        // Act
        $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::REEVALUATE_TAX_URI, $faker->uuid())
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $response = $this->client->getCrawler();

        $title = $response->filter('h1')->text();

        self::assertEquals('Page non trouvée', $title);
    }

    public function testCancelDuringTaxRevaluation(): void
    {
        // Arrange
        /** @var TaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(TaxRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $tax = TaxFactory::createOne(['name' => 'TVA taux normal', 'rate' => 20.0]);
        $taxes = $taxRepository->getAllTaxes();
        self::assertCount(1, $taxes);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::REEVALUATE_TAX_URI, $tax->_real()->uuid())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans('admin.tax.revaluate.titlePage', ['%taxName%' => $tax->_real()->name()])
        );

        $cancelLink = $crawler->selectLink($translator->trans('cancel'));
        self::assertCount(1, $cancelLink, 'Cancel link should exist');

        $this->client->click($cancelLink->link());

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertRouteSame(GetTaxesController::ROUTE_NAME);

        /** @var Tax $taxAfterCancel */
        $taxAfterCancel = $taxRepository->getById($tax->_real()->uuid());
        self::assertSame($tax->_real()->name(), $taxAfterCancel->name()->toString());
        self::assertSame($tax->_real()->rate(), $taxAfterCancel->rate());

        $taxes = $taxRepository->getAllTaxes();
        self::assertCount(1, $taxes);
    }
}
