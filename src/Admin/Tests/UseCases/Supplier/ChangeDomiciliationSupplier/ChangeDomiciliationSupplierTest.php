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

namespace Admin\Tests\UseCases\Supplier\ChangeDomiciliationSupplier;

use Admin\Entities\Exception\Supplier\SupplierNotFound;
use Admin\Entities\Repository\SupplierRepository;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Admin\UseCases\Supplier\ChangeDomiciliationSupplier\ChangeDomiciliationSupplier;
use Admin\UseCases\Supplier\ChangeDomiciliationSupplier\ChangeDomiciliationSupplierRequest;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 */
final class ChangeDomiciliationSupplierTest extends TestCase
{
    public function testChangeDomiciliationSupplierWithSucceed(): void
    {
        // Arrange
        $supplierRepository = $this->createMock(SupplierRepository::class);
        $useCase = new ChangeDomiciliationSupplier($supplierRepository);
        $request = $this->createMock(ChangeDomiciliationSupplierRequest::class);
        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')->build();
        $supplier = (new SupplierDataBuilder())->create('Supplier 1', $familyLog)->build();

        $request->expects(self::once())->method('address')->willReturn('25, rue des Fleurs');
        $request->expects(self::once())->method('postalCode')->willReturn('75000');
        $request->expects(self::once())->method('town')->willReturn('Paris');
        $request->expects(self::once())->method('country')->willReturn('France');
        $request->expects(self::once())->method('phone')->willReturn('+33170000000');
        $request->expects(self::once())->method('email')->willReturn('test@test.fr');
        $request->expects(self::once())->method('slug')->willReturn('supplier-1');

        $supplierRepository->expects(self::once())
            ->method('getBySlug')
            ->with('supplier-1')
            ->willReturn($supplier)
        ;

        $supplierRepository->expects(self::once())
            ->method('changeDomiciliation')
            ->with($supplier)
        ;

        // Act
        $response = $useCase->execute($request);
        $supplierUpdated = $response->supplier;

        // Assert
        self::assertSame("25, rue des Fleurs\n75000 Paris, France", $supplierUpdated->address()->getFullAddress());
        self::assertSame('+33170000000', $supplierUpdated->phone()->toNumber());
    }

    public function testChangeDomiciliationSupplierFailWithSupplierNotFoundException(): void
    {
        // Arrange
        $supplierRepository = $this->createMock(SupplierRepository::class);
        $useCase = new ChangeDomiciliationSupplier($supplierRepository);
        $request = $this->createMock(ChangeDomiciliationSupplierRequest::class);
        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')->build();
        $supplier = (new SupplierDataBuilder())->create('Supplier 1', $familyLog)->build();

        $request->expects(self::never())->method('address')->willReturn('25, rue des Fleurs');
        $request->expects(self::never())->method('postalCode')->willReturn('75000');
        $request->expects(self::never())->method('town')->willReturn('Paris');
        $request->expects(self::never())->method('country')->willReturn('France');
        $request->expects(self::never())->method('phone')->willReturn('+33170000000');
        $request->expects(self::never())->method('email')->willReturn('test@test.fr');
        $request->expects(self::once())->method('slug')->willReturn('supplier-1');

        $supplierRepository->expects(self::once())
            ->method('getBySlug')
            ->with('supplier-1')
            ->will(self::throwException(new SupplierNotFound($supplier->slug())))
        ;

        $supplierRepository->expects(self::never())
            ->method('changeDomiciliation')
        ;

        // Act && Assert
        $this->expectException(SupplierNotFound::class);
        $this->expectExceptionMessage(SupplierNotFound::MESSAGE);
        $useCase->execute($request);
    }
}
