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

namespace Admin\Tests\UseCases\Tax\CreateTax;

use Admin\Entities\Exception\TaxAlreadyExistsException;
use Admin\UseCases\Gateway\TaxRepository;
use Admin\UseCases\Tax\CreateTax\CreateTax;
use Admin\UseCases\Tax\CreateTax\CreateTaxRequest;
use PHPUnit\Framework\TestCase;

final class CreateTaxTest extends TestCase
{
    public function testCreateTaxWithSuccess(): void
    {
        // Arrange
        $taxRepository = $this->createMock(TaxRepository::class);
        $useCase = new CreateTax($taxRepository);
        $request = $this->createMock(CreateTaxRequest::class);

        $request->expects(self::once())->method('name')->willReturn('TVA taux normal');
        $request->expects(self::exactly(2))->method('rate')->willReturn(20.0);

        $taxRepository->expects(self::once())
            ->method('exists')
            ->with(20.0)
            ->willReturn(false)
        ;

        $taxRepository->expects(self::once())->method('save');

        // Act
        $response = $useCase->execute($request);
        $tax = $response->tax;

        // Assert
        self::assertSame('TVA taux normal', $tax->name()->toString());
        self::assertSame(0.2, $tax->rate());
    }

    public function testCreateTaxFailWithAlreadyExistsException(): void
    {
        // Arrange
        $taxRepository = $this->createMock(TaxRepository::class);
        $useCase = new CreateTax($taxRepository);
        $request = $this->createMock(CreateTaxRequest::class);

        $request->expects(self::once())->method('name')->willReturn('TVA taux normal');
        $request->expects(self::exactly(2))->method('rate')->willReturn(20.0);

        $taxRepository->expects(self::once())
            ->method('exists')
            ->with(20.0)
            ->willReturn(true)
        ;

        $taxRepository->expects(self::never())->method('save');

        // Act && Assert
        $this->expectException(TaxAlreadyExistsException::class);
        $this->expectExceptionMessage(TaxAlreadyExistsException::MESSAGE);
        $useCase->execute($request);
    }
}
