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

use Admin\Entities\Repository\SupplierRepository;
use Admin\Entities\Supplier\SupplierCollection;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Admin\UseCases\Supplier\GetSuppliers\GetSuppliers;
use Admin\UseCases\Supplier\GetSuppliers\GetSuppliersRequest;
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
        $useCase = new GetSuppliers($supplierRepository);
        $request = $this->createMock(GetSuppliersRequest::class);

        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')->build();
        $supplierBuilder = new SupplierDataBuilder();
        $supplier1 = $supplierBuilder->create('Supplier1', $familyLog)->build();
        $supplier2 = $supplierBuilder->create('Supplier2', $familyLog)->build();

        $suppliers = new SupplierCollection(totalItems: 2);
        $suppliers->add($supplier1);
        $suppliers->add($supplier2);

        $request->expects(self::once())->method('page')->willReturn(1);
        $request->expects(self::once())->method('itemsPerPage')->willReturn(10);

        $supplierRepository->expects(self::once())->method('getAllSuppliersPaginated')->willReturn($suppliers);

        // Act
        $response = $useCase->execute($request);
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
