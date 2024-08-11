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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\Supplier\ChangeDeliverySpecification;

use Admin\Adapters\Gateway\ORM\Entity\Supplier;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineSupplierRepository;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group functionalTest
 */
final class ChangeDeliverySpecificationSupplierControllerTest extends WebTestCase
{
    private const CHANGE_DELIVERY_SPECIFICATION_SUPPLIER_URI = '/admin/suppliers/%s/change-delivery-specification';

    public function testChangeDeliverySpecificationSupplierWillSucceed(): void
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
        $crawler = $client->request(
            Request::METHOD_GET,
            sprintf(self::CHANGE_DELIVERY_SPECIFICATION_SUPPLIER_URI, $supplier->uuid()->toString())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Change delivery specification "Supplier 1"');

        $form = $crawler->selectButton('Update')->form([
            'changeDeliverySpecificationSupplier[familyLog]' => $familyLog2->uuid()->toString(),
            'changeDeliverySpecificationSupplier[delayDelivery]' => 2,
            'changeDeliverySpecificationSupplier[orderDays][0]' => true,
            'changeDeliverySpecificationSupplier[orderDays][1]' => false,
            'changeDeliverySpecificationSupplier[orderDays][3]' => true,
            'changeDeliverySpecificationSupplier[orderDays][4]' => false,
            'changeDeliverySpecificationSupplier[orderDays][5]' => true,
            'changeDeliverySpecificationSupplier[slug]' => 'supplier-1',
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/suppliers');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertEquals('Supplier updated', $flash);

        /** @var Supplier $supplierUpdated */
        $supplierUpdated = $supplierRepository->findOneBy(['slug' => 'supplier-1']);
        self::assertSame('Frais', $supplierUpdated->familyLog()->label());
        self::assertSame(2, $supplierUpdated->delayDelivery());
        self::assertSame([0, 3, 5], $supplierUpdated->orderDays());
    }

    public function testChangeDeliverySpecificationSupplierFailWithSupplierNotFound(): void
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
            sprintf(self::CHANGE_DELIVERY_SPECIFICATION_SUPPLIER_URI, $faker->uuid())
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $response = $client->getCrawler();

        $title = $response->filter('h1')->text();

        self::assertEquals('Page non trouvée', $title);
    }
}
