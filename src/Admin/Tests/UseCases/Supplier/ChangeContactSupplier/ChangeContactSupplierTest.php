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

namespace Admin\Tests\UseCases\Supplier\ChangeContactSupplier;

use Admin\Entities\Exception\SupplierNotFoundException;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Admin\UseCases\Gateway\SupplierRepository;
use Admin\UseCases\Supplier\ChangeContactSupplier\ChangeContactSupplier;
use Admin\UseCases\Supplier\ChangeContactSupplier\ChangeContactSupplierRequest;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 */
final class ChangeContactSupplierTest extends TestCase
{
    public function testChangeContactSupplierWillSucceed(): void
    {
        // Arrange
        $supplierRepository = $this->createMock(SupplierRepository::class);
        $useCase = new ChangeContactSupplier($supplierRepository);
        $request = $this->createMock(ChangeContactSupplierRequest::class);
        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')->build();
        $supplier = (new SupplierDataBuilder())->create('Supplier 1', $familyLog)->build();

        $request->expects(self::once())->method('contact')->willReturn('David');
        $request->expects(self::once())->method('cellphone')->willReturn('+33600000001');
        $request->expects(self::once())->method('slug')->willReturn('supplier-1');

        $supplierRepository->expects(self::once())
            ->method('findBySlug')
            ->with('supplier-1')
            ->willReturn($supplier)
        ;

        $supplierRepository->expects(self::once())
            ->method('changeContact')
            ->with($supplier)
        ;

        // Act
        $response = $useCase->execute($request);
        $supplierUpdated = $response->supplier;

        // Assert
        self::assertSame('David', $supplierUpdated->contact());
        self::assertSame('+33600000001', $supplierUpdated->cellphone()->toNumber());
    }

    public function testChangeContactSupplierFailWithSupplierNotFoundException(): void
    {
        // Arrange
        $supplierRepository = $this->createMock(SupplierRepository::class);
        $useCase = new ChangeContactSupplier($supplierRepository);
        $request = $this->createMock(ChangeContactSupplierRequest::class);
        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')->build();
        $supplier = (new SupplierDataBuilder())->create('Supplier 1', $familyLog)->build();

        $request->expects(self::never())->method('contact')->willReturn('David');
        $request->expects(self::never())->method('cellphone')->willReturn('+33600000001');
        $request->expects(self::once())->method('slug')->willReturn('supplier-1');

        $supplierRepository->expects(self::once())
            ->method('findBySlug')
            ->with('supplier-1')
            ->will(self::throwException(new SupplierNotFoundException($supplier->slug())))
        ;

        $supplierRepository->expects(self::never())
            ->method('changeContact')
        ;

        // Act && Assert
        $this->expectException(SupplierNotFoundException::class);
        $this->expectExceptionMessage(SupplierNotFoundException::MESSAGE);
        $useCase->execute($request);
    }
}
