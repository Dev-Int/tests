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

namespace Admin\Tests\Adapters\controller\Symfony\Controller\Supplier\RenameSupplier;

use Admin\Adapters\Gateway\ORM\Entity\Supplier;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineSupplierRepository;
use Admin\Entities\Exception\SupplierAlreadyExists;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group functionalTest
 */
final class RenameSupplierControllerTest extends WebTestCase
{
    private const RENAME_SUPPLIER_URI = '/admin/suppliers/%s/rename';

    public function testRenameSupplierWillSucceed(): void
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
            sprintf(self::RENAME_SUPPLIER_URI, $supplier->uuid()->toString())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Rename "Supplier 1"');

        $form = $crawler->selectButton('Rename')->form([
            'renameSupplier[name]' => 'Supplier new',
            'renameSupplier[slug]' => 'supplier-1',
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/suppliers');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container')->children('div.flash.flash-success')->text();

        self::assertSame('Supplier updated', $flash);

        /** @var Supplier $supplierUpdated */
        $supplierUpdated = $supplierRepository->findOneBy(['slug' => 'supplier-new']);
        self::assertSame('Supplier new', $supplierUpdated->name());
        $suppliers = $supplierRepository->findAllSuppliers();
        self::assertCount(1, $suppliers);
    }

    public function testRenameSupplierFailWithAlreadyExistsException(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineSupplierRepository $supplierRepository */
        $supplierRepository = self::getContainer()->get(DoctrineSupplierRepository::class);

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);

        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')->build();
        $familyLogRepository->save($familyLog);
        $supplier1 = (new SupplierDataBuilder())->create('Supplier 1', $familyLog)->build();
        $supplier2 = (new SupplierDataBuilder())->create('Supplier new', $familyLog)
            ->withUuid('eca51cd2-4189-4a55-be7e-a6928cf1b5a8')
            ->build()
        ;
        $supplierRepository->save($supplier1);
        $supplierRepository->save($supplier2);

        // Act
        $crawler = $client->request(
            Request::METHOD_GET,
            sprintf(self::RENAME_SUPPLIER_URI, $supplier1->uuid()->toString())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Rename "Supplier 1"');

        $form = $crawler->selectButton('Rename')->form([
            'renameSupplier[name]' => 'Supplier new',
            'renameSupplier[slug]' => 'supplier-1',
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/suppliers');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container')->children('div.flash.flash-error')->text();

        self::assertSame(SupplierAlreadyExists::MESSAGE, $flash);
    }
}
