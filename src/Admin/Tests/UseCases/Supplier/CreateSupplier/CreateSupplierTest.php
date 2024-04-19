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

namespace Admin\Tests\UseCases\Supplier\CreateSupplier;

use Admin\Entities\Exception\SupplierAlreadyExists;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\UseCases\Gateway\SupplierRepository;
use Admin\UseCases\Supplier\CreateSupplier\CreateSupplier;
use Admin\UseCases\Supplier\CreateSupplier\CreateSupplierRequest;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 */
final class CreateSupplierTest extends TestCase
{
    public function testCreateSupplierSucceed(): void
    {
        // Arrange
        $supplierRepository = $this->createMock(SupplierRepository::class);
        $useCase = new CreateSupplier($supplierRepository);
        $request = $this->createMock(CreateSupplierRequest::class);
        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')->build();

        $request->expects(self::exactly(2))->method('name')->willReturn('Dev-Int Création');
        $request->expects(self::once())->method('address')->willReturn('5, rue des Plantes');
        $request->expects(self::once())->method('postalCode')->willReturn('75000');
        $request->expects(self::once())->method('town')->willReturn('Paris');
        $request->expects(self::once())->method('country')->willReturn('France');
        $request->expects(self::once())->method('phone')->willReturn('+33297000000');
        $request->expects(self::once())->method('email')->willReturn('test@test.fr');
        $request->expects(self::once())->method('contact')->willReturn('Laurent');
        $request->expects(self::once())->method('cellphone')->willReturn('+33600000000');
        $request->expects(self::once())->method('familyLog')->willReturn($familyLog);
        $request->expects(self::once())->method('delayDelivery')->willReturn(3);
        $request->expects(self::once())->method('orderDays')->willReturn([1, 4]);

        $supplierRepository->expects(self::once())
            ->method('exists')
            ->with('Dev-Int Création')
            ->willReturn(false)
        ;
        $supplierRepository->expects(self::once())->method('save');

        // Act
        $response = $useCase->execute($request);
        $supplier = $response->supplier;

        // Assert
        self::assertSame('Dev-Int Création', $supplier->name()->toString());
        self::assertSame("5, rue des Plantes\n75000 Paris, France", $supplier->address()->getFullAddress());
        self::assertSame('+33297000000', $supplier->phone()->toNumber());
        self::assertSame('test@test.fr', $supplier->email()->toString());
        self::assertSame('Laurent', $supplier->contact());
        self::assertSame('dev-int-creation', $supplier->slug());
        self::assertSame('+33600000000', $supplier->cellphone()->toNumber());
        self::assertSame('Surgelé', $supplier->familyLog()->label()->toString());
        self::assertSame(3, $supplier->delayDelivery());
        self::assertSame([1, 4], $supplier->orderDays());
        self::assertTrue($supplier->active());
    }

    public function testCreateSupplierThrowAlreadyExistsException(): void
    {
        // Arrange
        $supplierRepository = $this->createMock(SupplierRepository::class);
        $useCase = new CreateSupplier($supplierRepository);
        $request = $this->createMock(CreateSupplierRequest::class);
        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')->build();

        $request->expects(self::exactly(2))->method('name')->willReturn('Dev-Int Création');
        $request->expects(self::never())->method('address')->willReturn('5, rue des Plantes');
        $request->expects(self::never())->method('postalCode')->willReturn('75000');
        $request->expects(self::never())->method('town')->willReturn('Paris');
        $request->expects(self::never())->method('country')->willReturn('France');
        $request->expects(self::never())->method('phone')->willReturn('+33297000000');
        $request->expects(self::never())->method('email')->willReturn('test@test.fr');
        $request->expects(self::never())->method('contact')->willReturn('Laurent');
        $request->expects(self::never())->method('cellphone')->willReturn('+33600000000');
        $request->expects(self::never())->method('familyLog')->willReturn($familyLog);
        $request->expects(self::never())->method('delayDelivery')->willReturn(3);
        $request->expects(self::never())->method('orderDays')->willReturn([1, 4]);

        $supplierRepository->expects(self::once())
            ->method('exists')
            ->with('Dev-Int Création')
            ->willReturn(true)
        ;
        $supplierRepository->expects(self::never())->method('save');

        // Act && Assert
        $this->expectException(SupplierAlreadyExists::class);
        $this->expectExceptionMessage(SupplierAlreadyExists::MESSAGE);

        $useCase->execute($request);
    }
}
