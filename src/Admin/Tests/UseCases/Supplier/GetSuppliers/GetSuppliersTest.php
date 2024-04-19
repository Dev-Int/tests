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

namespace Admin\Tests\UseCases\Supplier\GetSuppliers;

use Admin\Entities\Supplier\SupplierCollection;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Admin\UseCases\Gateway\SupplierRepository;
use Admin\UseCases\Supplier\GetSuppliers\GetSuppliers;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 */
final class GetSuppliersTest extends TestCase
{
    public function testGetSuppliersWillSucceed(): void
    {
        // Arrange
        $supplierRepository = $this->createMock(SupplierRepository::class);
        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')->build();
        $supplierBuilder = new SupplierDataBuilder();
        $supplier1 = $supplierBuilder->create('Supplier1', $familyLog)->build();
        $supplier2 = $supplierBuilder->create('Supplier2', $familyLog)->build();
        $suppliers = new SupplierCollection();
        $suppliers->add($supplier1);
        $suppliers->add($supplier2);

        $supplierRepository->expects(self::once())->method('findAllSupplier')->willReturn($suppliers);

        $useCase = new GetSuppliers($supplierRepository);

        // Act
        $response = $useCase->execute();
        $getSuppliers = $response->suppliers;

        // Assert
        self::assertCount(2, $getSuppliers);
        $getSupplier1 = $getSuppliers->current();
        self::assertSame('Supplier1', $getSupplier1->name()->toString());
        self::assertSame('supplier1', $getSupplier1->slug());
        $getSuppliers->next();
        $getSupplier2 = $getSuppliers->current();
        self::assertSame('Supplier2', $getSupplier2->name()->toString());
        self::assertSame('supplier2', $getSupplier2->slug());
    }
}
