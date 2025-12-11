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

namespace Admin\Tests\UseCases\Tax\RenameTax;

use Admin\Entities\Exception\Tax\TaxAlreadyExists;
use Admin\Entities\Exception\Tax\TaxNotFound;
use Admin\Entities\Repository\TaxRepository;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Admin\UseCases\Tax\RenameTax\RenameTax;
use Admin\UseCases\Tax\RenameTax\RenameTaxRequest;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 */
final class RenameTaxTest extends TestCase
{
    public function testRenameTaxWithSuccess(): void
    {
        // Arrange
        $taxRepository = $this->createMock(TaxRepository::class);
        $useCase = new RenameTax($taxRepository);
        $tax = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $request = $this->createMock(RenameTaxRequest::class);

        $request->expects(self::exactly(2))->method('name')->willReturn('TVA taux réduit');
        $request->expects(self::once())->method('uuid')->willReturn(TaxDataBuilder::UUID_VALID);

        $taxRepository->expects(self::once())
            ->method('exists')
            ->with('TVA taux réduit', 0.2)
            ->willReturn(false)
        ;

        $taxRepository->expects(self::once())
            ->method('getById')
            ->with(TaxDataBuilder::UUID_VALID)
            ->willReturn($tax)
        ;

        $taxRepository->expects(self::once())->method('rename');

        // Act
        $response = $useCase->execute($request);
        $tax = $response->tax;

        // Assert
        self::assertSame('TVA taux réduit', $tax->name()->toString());
        self::assertSame(0.2, $tax->rate());
    }

    public function testRenameTaxFailWithAlreadyExistsException(): void
    {
        // Arrange
        $taxRepository = $this->createMock(TaxRepository::class);
        $useCase = new RenameTax($taxRepository);
        $tax = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $request = $this->createMock(RenameTaxRequest::class);

        $request->expects(self::exactly(2))->method('name')->willReturn('TVA taux réduit');
        $request->expects(self::once())->method('uuid')->willReturn(TaxDataBuilder::UUID_VALID);

        $taxRepository->expects(self::once())
            ->method('getById')
            ->with(TaxDataBuilder::UUID_VALID)
            ->willReturn($tax)
        ;

        $taxRepository->expects(self::once())
            ->method('exists')
            ->with('TVA taux réduit', 0.2)
            ->willReturn(true)
        ;

        $taxRepository->expects(self::never())->method('rename');

        // Act && Assert
        $this->expectException(TaxAlreadyExists::class);
        $this->expectExceptionMessage(TaxAlreadyExists::MESSAGE);
        $useCase->execute($request);
    }

    public function testRenameTaxFailWithTaxNotFoundException(): void
    {
        // Arrange
        $taxRepository = $this->createMock(TaxRepository::class);
        $useCase = new RenameTax($taxRepository);
        (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $request = $this->createMock(RenameTaxRequest::class);

        $request->expects(self::never())->method('name')->willReturn('TVA taux réduit');
        $request->expects(self::once())->method('uuid')->willReturn(TaxDataBuilder::UUID_VALID);

        $taxRepository->expects(self::once())
            ->method('getById')
            ->with(TaxDataBuilder::UUID_VALID)
            ->will(self::throwException(new TaxNotFound(TaxDataBuilder::UUID_VALID)))
        ;

        $taxRepository->expects(self::never())
            ->method('exists')
        ;

        $taxRepository->expects(self::never())->method('rename');

        // Act && Assert
        $this->expectException(TaxNotFound::class);
        $this->expectExceptionMessage(TaxNotFound::MESSAGE);
        $useCase->execute($request);
    }
}
