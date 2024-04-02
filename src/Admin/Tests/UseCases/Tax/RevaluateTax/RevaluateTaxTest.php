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

namespace Admin\Tests\UseCases\Tax\RevaluateTax;

use Admin\Entities\Exception\TaxAlreadyExistsException;
use Admin\Entities\Exception\TaxNotFoundException;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Admin\UseCases\Gateway\TaxRepository;
use Admin\UseCases\Tax\RevaluateTax\RevaluateTax;
use Admin\UseCases\Tax\RevaluateTax\RevaluateTaxRequest;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 */
final class RevaluateTaxTest extends TestCase
{
    public function testRevaluateTaxWithSuccess(): void
    {
        // Arrange
        $taxRepository = $this->createMock(TaxRepository::class);
        $useCase = new RevaluateTax($taxRepository);
        $tax = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $request = $this->createMock(RevaluateTaxRequest::class);

        $request->expects(self::exactly(2))->method('rate')->willReturn(10.0);
        $request->expects(self::once())->method('uuid')->willReturn(TaxDataBuilder::UUID_VALID);

        $taxRepository->expects(self::once())
            ->method('findById')
            ->with(TaxDataBuilder::UUID_VALID)
            ->willReturn($tax)
        ;

        $taxRepository->expects(self::once())
            ->method('exists')
            ->with('TVA taux normal', 10.0)
            ->willReturn(false)
        ;

        $taxRepository->expects(self::once())->method('revaluate');

        // Act
        $response = $useCase->execute($request);
        $taxReevaluate = $response->tax;

        // Assert
        self::assertSame('TVA taux normal', $tax->name()->toString());
        self::assertSame(0.1, $taxReevaluate->rate());
    }

    public function testRevaluateTaxFailWithAlreadyExistsException(): void
    {
        // Arrange
        $taxRepository = $this->createMock(TaxRepository::class);
        $useCase = new RevaluateTax($taxRepository);
        $tax = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $request = $this->createMock(RevaluateTaxRequest::class);

        $request->expects(self::exactly(2))->method('rate')->willReturn(10.0);
        $request->expects(self::once())->method('uuid')->willReturn(TaxDataBuilder::UUID_VALID);

        $taxRepository->expects(self::once())
            ->method('findById')
            ->with(TaxDataBuilder::UUID_VALID)
            ->willReturn($tax)
        ;

        $taxRepository->expects(self::once())
            ->method('exists')
            ->with('TVA taux normal', 10.0)
            ->willReturn(true)
        ;

        $taxRepository->expects(self::never())->method('revaluate');

        // Act && Assert
        $this->expectException(TaxAlreadyExistsException::class);
        $this->expectExceptionMessage(TaxAlreadyExistsException::MESSAGE);
        $useCase->execute($request);
    }

    public function testRevaluateTaxFailWithTaxNotFoundException(): void
    {
        // Arrange
        $taxRepository = $this->createMock(TaxRepository::class);
        $useCase = new RevaluateTax($taxRepository);
        $request = $this->createMock(RevaluateTaxRequest::class);

        $request->expects(self::never())->method('rate')->willReturn(10.0);
        $request->expects(self::once())->method('uuid')->willReturn(TaxDataBuilder::UUID_VALID);

        $taxRepository->expects(self::once())
            ->method('findById')
            ->with(TaxDataBuilder::UUID_VALID)
            ->will(self::throwException(new TaxNotFoundException(TaxDataBuilder::UUID_VALID)))
        ;

        $taxRepository->expects(self::never())
            ->method('exists')
        ;

        $taxRepository->expects(self::never())->method('revaluate');

        // Act && Assert
        $this->expectException(TaxNotFoundException::class);
        $this->expectExceptionMessage(TaxNotFoundException::MESSAGE);
        $useCase->execute($request);
    }
}
