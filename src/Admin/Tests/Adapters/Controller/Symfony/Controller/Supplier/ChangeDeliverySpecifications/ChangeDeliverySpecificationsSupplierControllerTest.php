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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\Supplier\ChangeDeliverySpecifications;

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
final class ChangeDeliverySpecificationsSupplierControllerTest extends BaseFunctionalTestCase
{
    use Factories;

    private const CHANGE_DELIVERY_SPECIFICATIONS_SUPPLIER_URI = '/admin/suppliers/%s/change-delivery-specifications';

    public function testChangeDeliverySpecificationsSupplierWillSucceed(): void
    {
        // Arrange
        /** @var SupplierRepository $supplierRepository */
        $supplierRepository = self::getContainer()->get(SupplierRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $familyLog = FamilyLogFactory::createOne(['label' => 'Surgelé']);
        $familyLog2 = FamilyLogFactory::createOne(['label' => 'Frais']);
        $supplier = SupplierFactory::createOne([
            'name' => 'Supplier 1',
            'familyLog' => $familyLog->_real(),
        ]);
        $suppliers = $supplierRepository->getAllSuppliers();
        self::assertCount(1, $suppliers);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_DELIVERY_SPECIFICATIONS_SUPPLIER_URI, $supplier->_real()->uuid())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans(
                'admin.supplier.changeDeliverySpecifications.titlePage',
                ['%supplierName%' => $supplier->_real()->name()]
            )
        );

        $form = $crawler->selectButton($translator->trans('admin.supplier.changeDeliverySpecifications.button'))->form([
            'changeDeliverySpecificationsSupplier[familyLog]' => $familyLog2->_real()->uuid(),
            'changeDeliverySpecificationsSupplier[delayDelivery]' => 2,
            'changeDeliverySpecificationsSupplier[orderDays][0]' => true,
            'changeDeliverySpecificationsSupplier[orderDays][1]' => false,
            'changeDeliverySpecificationsSupplier[orderDays][3]' => true,
            'changeDeliverySpecificationsSupplier[orderDays][4]' => false,
            'changeDeliverySpecificationsSupplier[orderDays][5]' => true,
            'changeDeliverySpecificationsSupplier[slug]' => 'supplier-1',
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/suppliers');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertEquals($translator->trans('admin.supplier.changeDeliverySpecifications.success'), $flash);

        /** @var SupplierDomain $supplierUpdated */
        $supplierUpdated = $supplierRepository->getBySlug('supplier-1');
        self::assertSame('Frais', $supplierUpdated->familyLog()->label()->toString());
        self::assertSame(2, $supplierUpdated->delayDelivery());
        self::assertSame(
            [0, 3, 5],
            $supplierUpdated->orderDays(),
            'We expect the days to be ordered as an integer separated by a space'
        );
    }

    public function testChangeDeliverySpecificationsSupplierFailWithSupplierNotFound(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

        /** @var SupplierRepository $supplierRepository */
        $supplierRepository = self::getContainer()->get(SupplierRepository::class);

        $familyLog = FamilyLogFactory::createOne(['label' => 'Surgelé']);
        FamilyLogFactory::createOne(['label' => 'Frais']);
        SupplierFactory::createOne([
            'name' => 'Supplier 1',
            'familyLog' => $familyLog->_real(),
        ]);
        $suppliers = $supplierRepository->getAllSuppliers();
        self::assertCount(1, $suppliers);

        // Act
        $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_DELIVERY_SPECIFICATIONS_SUPPLIER_URI, $faker->uuid())
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $response = $this->client->getCrawler();

        $title = $response->filter('h1')->text();

        self::assertEquals('Page non trouvée', $title);
    }
}
