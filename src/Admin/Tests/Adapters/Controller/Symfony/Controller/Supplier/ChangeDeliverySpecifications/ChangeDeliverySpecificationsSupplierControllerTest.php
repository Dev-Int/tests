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

use Admin\Adapters\Gateway\ORM\Entity\Supplier;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineSupplierRepository;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group functionalTest
 */
final class ChangeDeliverySpecificationsSupplierControllerTest extends WebTestCase
{
    private const CHANGE_DELIVERY_SPECIFICATIONS_SUPPLIER_URI = '/admin/suppliers/%s/change-delivery-specifications';

    public function testChangeDeliverySpecificationsSupplierWillSucceed(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $client = self::createClient();

        /** @var DoctrineSupplierRepository $supplierRepository */
        $supplierRepository = self::getContainer()->get(DoctrineSupplierRepository::class);

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')->build();
        $familyLog2 = (new FamilyLogDataBuilder())->create('Frais')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLogRepository->save($familyLog);
        $familyLogRepository->save($familyLog2);
        $supplier = (new SupplierDataBuilder())->create('Supplier 1', $familyLog)->build();
        $supplierRepository->save($supplier);
        $suppliers = $supplierRepository->findAllSuppliers();
        self::assertCount(1, $suppliers);

        // Act
        $crawler = $client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_DELIVERY_SPECIFICATIONS_SUPPLIER_URI, $supplier->uuid()->toString())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans(
                'admin.supplier.changeDeliverySpecifications.titlePage',
                ['%supplierName%' => $supplier->name()->toString()]
            )
        );

        $form = $crawler->selectButton($translator->trans('admin.supplier.changeDeliverySpecifications.button'))->form([
            'changeDeliverySpecificationsSupplier[familyLog]' => $familyLog2->uuid()->toString(),
            'changeDeliverySpecificationsSupplier[delayDelivery]' => 2,
            'changeDeliverySpecificationsSupplier[orderDays][0]' => true,
            'changeDeliverySpecificationsSupplier[orderDays][1]' => false,
            'changeDeliverySpecificationsSupplier[orderDays][3]' => true,
            'changeDeliverySpecificationsSupplier[orderDays][4]' => false,
            'changeDeliverySpecificationsSupplier[orderDays][5]' => true,
            'changeDeliverySpecificationsSupplier[slug]' => 'supplier-1',
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/suppliers');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertEquals($translator->trans('admin.supplier.changeDeliverySpecifications.success'), $flash);

        /** @var Supplier $supplierUpdated */
        $supplierUpdated = $supplierRepository->findOneBy(['slug' => 'supplier-1']);
        self::assertSame('Frais', $supplierUpdated->familyLog()->label());
        self::assertSame(2, $supplierUpdated->delayDelivery());
        self::assertSame([0, 3, 5], $supplierUpdated->orderDays());
    }

    public function testChangeDeliverySpecificationsSupplierFailWithSupplierNotFound(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $client = self::createClient();

        /** @var DoctrineSupplierRepository $supplierRepository */
        $supplierRepository = self::getContainer()->get(DoctrineSupplierRepository::class);

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);

        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')->build();
        $familyLog2 = (new FamilyLogDataBuilder())->create('Frais')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLogRepository->save($familyLog);
        $familyLogRepository->save($familyLog2);
        $supplier = (new SupplierDataBuilder())->create('Supplier 1', $familyLog)->build();
        $supplierRepository->save($supplier);
        $suppliers = $supplierRepository->findAllSuppliers();
        self::assertCount(1, $suppliers);

        // Act
        $client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_DELIVERY_SPECIFICATIONS_SUPPLIER_URI, $faker->uuid())
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $response = $client->getCrawler();

        $title = $response->filter('h1')->text();

        self::assertEquals('Page non trouvée', $title);
    }
}
