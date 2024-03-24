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

namespace Admin\Tests\UseCases\Unit\ChangeUnitLabel;

use Admin\Entities\Exception\UnitAlreadyExistsException;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use Admin\UseCases\Gateway\UnitRepository;
use Admin\UseCases\Unit\ChangeUnitLabel\ChangeUnitLabel;
use Admin\UseCases\Unit\ChangeUnitLabel\ChangeUnitLabelRequest;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 */
final class ChangeUnitLabelTest extends TestCase
{
    public function testChangeUnitLabelWithSuccess(): void
    {
        // Arrange
        $unitRepository = $this->createMock(UnitRepository::class);
        $useCase = new ChangeUnitLabel($unitRepository);
        $request = $this->createMock(ChangeUnitLabelRequest::class);
        $unit = (new UnitDataBuilder())->create('Kilogramme', 'kg')->build();

        $request->expects(self::exactly(2))->method('label')->willReturn('Kilogrammes');
        $request->expects(self::once())->method('abbreviation')->willReturn('kg');
        $request->expects(self::once())->method('slug')->willReturn('kilogramme');

        $unitRepository->expects(self::once())
            ->method('findBySlug')
            ->with('kilogramme')
            ->willReturn($unit)
        ;

        $unitRepository->expects(self::once())
            ->method('exists')
            ->willReturn(false)
        ;

        $unitRepository->expects(self::once())
            ->method('changeLabel')
            ->with($unit)
        ;

        // Act
        $response = $useCase->execute($request);
        $unitUpdated = $response->unit;

        // Assert
        self::assertSame('Kilogrammes', $unitUpdated->label()->toString());
        self::assertSame('kg', $unitUpdated->abbreviation());
        self::assertSame('kilogrammes', $unitUpdated->slug());
    }

    public function testChangeUnitAbbreviationWithSuccess(): void
    {
        // Arrange
        $unitRepository = $this->createMock(UnitRepository::class);
        $useCase = new ChangeUnitLabel($unitRepository);
        $request = $this->createMock(ChangeUnitLabelRequest::class);
        $unit = (new UnitDataBuilder())->create('Kilogramme', 'kg')->build();

        $request->expects(self::exactly(2))->method('label')->willReturn('Kilogramme');
        $request->expects(self::once())->method('abbreviation')->willReturn('KG');
        $request->expects(self::once())->method('slug')->willReturn('kilogramme');

        $unitRepository->expects(self::once())
            ->method('findBySlug')
            ->with('kilogramme')
            ->willReturn($unit)
        ;

        $unitRepository->expects(self::once())
            ->method('exists')
            ->willReturn(false)
        ;

        $unitRepository->expects(self::once())
            ->method('changeLabel')
            ->with($unit)
        ;

        // Act
        $response = $useCase->execute($request);
        $unitUpdated = $response->unit;

        // Assert
        self::assertSame('Kilogramme', $unitUpdated->label()->toString());
        self::assertSame('KG', $unitUpdated->abbreviation());
        self::assertSame('kilogramme', $unitUpdated->slug());
    }

    public function testChangeUnitLabelFailWithAlreadyExistsException(): void
    {
        // Arrange
        $unitRepository = $this->createMock(UnitRepository::class);
        $useCase = new ChangeUnitLabel($unitRepository);
        $request = $this->createMock(ChangeUnitLabelRequest::class);
        $unit = (new UnitDataBuilder())->create('Kilogramme', 'kg')->build();

        $request->expects(self::exactly(2))->method('label')->willReturn('Kilogrammes');
        $request->expects(self::never())->method('abbreviation')->willReturn('kg');
        $request->expects(self::once())->method('slug')->willReturn('kilogramme');

        $unitRepository->expects(self::once())
            ->method('findBySlug')
            ->with('kilogramme')
            ->willReturn($unit)
        ;

        $unitRepository->expects(self::once())
            ->method('exists')
            ->willReturn(true)
        ;

        $unitRepository->expects(self::never())
            ->method('changeLabel')
            ->with($unit)
        ;

        // Act
        $this->expectException(UnitAlreadyExistsException::class);
        $this->expectExceptionMessage(UnitAlreadyExistsException::MESSAGE);
        $useCase->execute($request);
    }
}
