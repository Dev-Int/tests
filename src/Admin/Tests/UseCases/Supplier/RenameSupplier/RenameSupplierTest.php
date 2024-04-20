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

namespace Admin\Tests\UseCases\Supplier\RenameSupplier;

use Admin\Entities\Exception\SupplierAlreadyExists;
use Admin\Entities\Exception\SupplierNotFoundException;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Admin\UseCases\Gateway\SupplierRepository;
use Admin\UseCases\Supplier\RenameSupplier\RenameSupplier;
use Admin\UseCases\Supplier\RenameSupplier\RenameSupplierRequest;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 */
final class RenameSupplierTest extends TestCase
{
    public function testRenameSupplierWithSuccess(): void
    {
        // Arrange
        $supplierRepository = $this->createMock(SupplierRepository::class);
        $useCase = new RenameSupplier($supplierRepository);
        $request = $this->createMock(RenameSupplierRequest::class);
        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')->build();
        $supplier = (new SupplierDataBuilder())->create('Supplier 1', $familyLog)->build();

        $request->expects(self::once())->method('slug')->willReturn('supplier-1');
        $request->expects(self::exactly(2))->method('name')->willReturn('Supplier 2');

        $supplierRepository->expects(self::once())
            ->method('exists')
            ->with('Supplier 2')
            ->willReturn(false)
        ;

        $supplierRepository->expects(self::once())
            ->method('findBySlug')
            ->with('supplier-1')
            ->willReturn($supplier)
        ;

        $supplierRepository->expects(self::once())
            ->method('renameSupplier')
            ->with($supplier)
        ;

        // Act
        $response = $useCase->execute($request);
        $supplierUpdated = $response->supplier;

        // Assert
        self::assertSame('Supplier 2', $supplierUpdated->name()->toString());
        self::assertSame('supplier-2', $supplierUpdated->slug());
    }

    public function testRenameSupplierFailWithAlreadyExistsException(): void
    {
        $supplierRepository = $this->createMock(SupplierRepository::class);
        $useCase = new RenameSupplier($supplierRepository);
        $request = $this->createMock(RenameSupplierRequest::class);
        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')->build();
        (new SupplierDataBuilder())->create('Supplier 1', $familyLog)->build();

        $request->expects(self::never())->method('slug')->willReturn('supplier-1');
        $request->expects(self::exactly(2))->method('name')->willReturn('Supplier 2');

        $supplierRepository->expects(self::once())
            ->method('exists')
            ->with('Supplier 2')
            ->willReturn(true)
        ;

        $supplierRepository->expects(self::never())
            ->method('findBySlug')
        ;

        $supplierRepository->expects(self::never())
            ->method('renameSupplier')
        ;

        // Act && Assert
        $this->expectException(SupplierAlreadyExists::class);
        $this->expectExceptionMessage(SupplierAlreadyExists::MESSAGE);
        $useCase->execute($request);
    }

    public function testRenameSupplierFailWithSupplierNotFoundException(): void
    {
        $supplierRepository = $this->createMock(SupplierRepository::class);
        $useCase = new RenameSupplier($supplierRepository);
        $request = $this->createMock(RenameSupplierRequest::class);
        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')->build();
        (new SupplierDataBuilder())->create('Supplier 1', $familyLog)->build();

        $request->expects(self::once())->method('slug')->willReturn('supplier-1');
        $request->expects(self::once())->method('name')->willReturn('Supplier 2');

        $supplierRepository->expects(self::once())
            ->method('exists')
            ->with('Supplier 2')
            ->willReturn(false)
        ;

        $supplierRepository->expects(self::once())
            ->method('findBySlug')
            ->with('supplier-1')
            ->will(self::throwException(new SupplierNotFoundException('supplier-1')))
        ;

        $supplierRepository->expects(self::never())
            ->method('renameSupplier')
        ;

        // Act && Assert
        $this->expectException(SupplierNotFoundException::class);
        $this->expectExceptionMessage(SupplierNotFoundException::MESSAGE);
        $useCase->execute($request);
    }
}
