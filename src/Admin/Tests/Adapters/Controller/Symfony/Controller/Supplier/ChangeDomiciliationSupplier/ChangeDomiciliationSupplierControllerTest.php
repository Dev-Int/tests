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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\Supplier\ChangeDomiciliationSupplier;

use Admin\Entities\Repository\SupplierRepository;
use Admin\Entities\Supplier\Supplier as SupplierDomain;
use Admin\Tests\Factory\FamilyLogFactory;
use Admin\Tests\Factory\SupplierFactory;
use Faker\Factory;
use Shared\Tests\BaseFunctionalTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 */
final class ChangeDomiciliationSupplierControllerTest extends BaseFunctionalTestCase
{
    use Factories;

    public const CHANGE_DOMICILIATION_SUPPLIER_URI = '/admin/suppliers/%s/change-domiciliation';

    public function testChangeDomiciliationWillSucceed(): void
    {
        // Arrange
        /** @var SupplierRepository $supplierRepository */
        $supplierRepository = self::getContainer()->get(SupplierRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $familyLog = FamilyLogFactory::createOne(['label' => 'Surgelé']);
        $supplier = SupplierFactory::createOne([
            'name' => 'Supplier 1',
            'familyLog' => $familyLog->_real(),
        ]);
        $suppliers = $supplierRepository->getAllSuppliers();
        self::assertCount(1, $suppliers);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_DOMICILIATION_SUPPLIER_URI, $supplier->_real()->uuid())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans(
                'admin.supplier.changeDomiciliation.titlePage',
                ['%supplierName%' => $supplier->_real()->name()],
            )
        );

        $form = $crawler->selectButton($translator->trans('admin.supplier.changeDomiciliation.button'))->form([
            'changeDomiciliationSupplier[address]' => '5, rue des Fleurs',
            'changeDomiciliationSupplier[postalCode]' => '45000',
            'changeDomiciliationSupplier[town]' => 'Orléans',
            'changeDomiciliationSupplier[country]' => 'France',
            'changeDomiciliationSupplier[phone]' => '+33238000000',
            'changeDomiciliationSupplier[email]' => 'test@test.fr',
            'changeDomiciliationSupplier[slug]' => 'supplier-1',
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/suppliers');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertEquals($translator->trans('admin.supplier.changeDomiciliation.success'), $flash);

        /** @var SupplierDomain $supplierUpdated */
        $supplierUpdated = $supplierRepository->getBySlug('supplier-1');
        self::assertSame('Supplier 1', $supplierUpdated->name()->toString());
        self::assertSame("5, rue des Fleurs\n45000 Orléans, France", $supplierUpdated->address()->getFullAddress());
        $suppliers = $supplierRepository->getAllSuppliers();
        self::assertCount(1, $suppliers->toArray());
    }

    public function testChangeDomiciliationFailWithSupplierNotFound(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

        /** @var SupplierRepository $supplierRepository */
        $supplierRepository = self::getContainer()->get(SupplierRepository::class);

        $familyLog = FamilyLogFactory::createOne(['label' => 'Surgelé']);
        SupplierFactory::createOne([
            'name' => 'Supplier 1',
            'familyLog' => $familyLog->_real(),
        ]);
        $suppliers = $supplierRepository->getAllSuppliers();
        self::assertCount(1, $suppliers);

        // Act
        $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_DOMICILIATION_SUPPLIER_URI, $faker->uuid())
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $response = $this->client->getCrawler();

        $title = $response->filter('h1')->text();

        self::assertEquals('Page non trouvée', $title);
    }
}
