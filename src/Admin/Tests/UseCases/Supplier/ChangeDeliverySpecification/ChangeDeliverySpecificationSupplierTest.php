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

namespace Admin\Tests\UseCases\Supplier\ChangeDeliverySpecification;

use Admin\Entities\Exception\SupplierNotFoundException;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Admin\UseCases\Gateway\SupplierRepository;
use Admin\UseCases\Supplier\ChangeDeliverySpecification\ChangeDeliverySpecificationSupplier;
use Admin\UseCases\Supplier\ChangeDeliverySpecification\ChangeDeliverySpecificationSupplierRequest;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 */
final class ChangeDeliverySpecificationSupplierTest extends TestCase
{
    public function testChangeDeliverySpecificationWithSuccess(): void
    {
        // Arrange
        $supplierRepository = $this->createMock(SupplierRepository::class);
        $useCase = new ChangeDeliverySpecificationSupplier($supplierRepository);
        $request = $this->createMock(ChangeDeliverySpecificationSupplierRequest::class);
        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')->build();
        $supplier = (new SupplierDataBuilder())->create('Supplier 1', $familyLog)->build();

        $request->expects(self::once())->method('familyLog')->willReturn($familyLog);
        $request->expects(self::once())->method('delayDelivery')->willReturn(3);
        $request->expects(self::once())->method('orderDays')->willReturn([1, 4]);
        $request->expects(self::once())->method('slug')->willReturn('supplier-1');

        $supplierRepository->expects(self::once())
            ->method('findBySlug')
            ->with('supplier-1')
            ->willReturn($supplier)
        ;

        $supplierRepository->expects(self::once())
            ->method('changeDeliverySpecification')
            ->with($supplier)
        ;

        // Act
        $response = $useCase->execute($request);
        $supplierUpdated = $response->supplier;

        // Assert
        self::assertSame('Surgelé', $supplierUpdated->familyLog()->label()->toString());
        self::assertSame(3, $supplierUpdated->delayDelivery());
        self::assertSame([1, 4], $supplierUpdated->orderDays());
    }

    public function testChangeDeliverySpecificationFailWithSupplierNotFoundException(): void
    {
        // Arrange
        $supplierRepository = $this->createMock(SupplierRepository::class);
        $useCase = new ChangeDeliverySpecificationSupplier($supplierRepository);
        $request = $this->createMock(ChangeDeliverySpecificationSupplierRequest::class);
        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')->build();
        $supplier = (new SupplierDataBuilder())->create('Supplier 1', $familyLog)->build();

        $request->expects(self::never())->method('familyLog')->willReturn($familyLog);
        $request->expects(self::never())->method('delayDelivery')->willReturn(3);
        $request->expects(self::never())->method('orderDays')->willReturn([1, 4]);
        $request->expects(self::once())->method('slug')->willReturn('supplier-1');

        $supplierRepository->expects(self::once())
            ->method('findBySlug')
            ->with('supplier-1')
            ->will(self::throwException(new SupplierNotFoundException($supplier->slug())))
        ;

        $supplierRepository->expects(self::never())
            ->method('changeDeliverySpecification')
        ;

        // Act && Assert
        $this->expectException(SupplierNotFoundException::class);
        $this->expectExceptionMessage(SupplierNotFoundException::MESSAGE);
        $useCase->execute($request);
    }
}
