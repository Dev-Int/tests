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

namespace Admin\Tests\Adapters\controller\Symfony\Controller\Supplier\ChangeDomiciliationSupplier;

use Admin\Adapters\Gateway\ORM\Entity\Supplier;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineSupplierRepository;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group functionalTest
 */
final class ChangeDomiciliationSupplierControllerTest extends WebTestCase
{
    public const CHANGE_DOMICILIATION_SUPPLIER_URI = '/admin/suppliers/%s/change-domiciliation';

    public function testChangeDomiciliationWillSucceed(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineSupplierRepository $supplierRepository */
        $supplierRepository = self::getContainer()->get(DoctrineSupplierRepository::class);

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);

        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')->build();
        $familyLogRepository->save($familyLog);
        $supplier = (new SupplierDataBuilder())->create('Supplier 1', $familyLog)->build();
        $supplierRepository->save($supplier);
        $suppliers = $supplierRepository->findAllSuppliers();
        self::assertCount(1, $suppliers);

        // Act
        $crawler = $client->request(
            Request::METHOD_GET,
            sprintf(self::CHANGE_DOMICILIATION_SUPPLIER_URI, $supplier->uuid()->toString())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Change domiciliation "Supplier 1"');

        $form = $crawler->selectButton('Update')->form([
            'changeDomiciliationSupplier[address]' => '5, rue des Fleurs',
            'changeDomiciliationSupplier[postalCode]' => '45000',
            'changeDomiciliationSupplier[town]' => 'Orléans',
            'changeDomiciliationSupplier[country]' => 'France',
            'changeDomiciliationSupplier[phone]' => '+33238000000',
            'changeDomiciliationSupplier[email]' => 'test@test.fr',
            'changeDomiciliationSupplier[slug]' => 'supplier-1',
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/suppliers');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container')->children('div.flash.flash-success')->text();

        self::assertEquals('Supplier updated', $flash);

        /** @var Supplier $supplierUpdated */
        $supplierUpdated = $supplierRepository->findOneBy(['slug' => 'supplier-1']);
        self::assertSame('Supplier 1', $supplierUpdated->name());
        self::assertSame("5, rue des Fleurs\n45000 Orléans, France", $supplierUpdated->fullAddress());
        $suppliers = $supplierRepository->findAllSuppliers();
        self::assertCount(1, $suppliers);
    }
}
