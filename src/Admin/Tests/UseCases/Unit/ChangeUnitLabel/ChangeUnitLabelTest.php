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

use Admin\Entities\Exception\Unit\UnitAlreadyExists;
use Admin\Entities\Exception\Unit\UnitNotFound;
use Admin\Entities\Repository\UnitRepository;
use Admin\Tests\DataBuilder\UnitDataBuilder;
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
            ->method('getBySlug')
            ->with('kilogramme')
            ->willReturn($unit)
        ;

        $unitRepository->expects(self::once())
            ->method('exists')
            ->with('Kilogrammes', $unit->uuid()->toString())
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
            ->method('getBySlug')
            ->with('kilogramme')
            ->willReturn($unit)
        ;

        $unitRepository->expects(self::once())
            ->method('exists')
            ->with('Kilogramme', $unit->uuid()->toString())
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
            ->method('getBySlug')
            ->with('kilogramme')
            ->willReturn($unit)
        ;

        $unitRepository->expects(self::once())
            ->method('exists')
            ->with('Kilogrammes', $unit->uuid()->toString())
            ->willReturn(true)
        ;

        $unitRepository->expects(self::never())
            ->method('changeLabel')
            ->with($unit)
        ;

        // Act
        $this->expectException(UnitAlreadyExists::class);
        $this->expectExceptionMessage(UnitAlreadyExists::MESSAGE);
        $useCase->execute($request);
    }

    public function testChangeUnitLabelFailWithUnitNotFoundException(): void
    {
        // Arrange
        $unitRepository = $this->createMock(UnitRepository::class);
        $useCase = new ChangeUnitLabel($unitRepository);
        $request = $this->createMock(ChangeUnitLabelRequest::class);
        $unit = (new UnitDataBuilder())->create('Kilogramme', 'kg')->build();

        $request->expects(self::never())->method('label')->willReturn('Kilogrammes');
        $request->expects(self::never())->method('abbreviation')->willReturn('kg');
        $request->expects(self::once())->method('slug')->willReturn('kilogramme');

        $unitRepository->expects(self::once())
            ->method('getBySlug')
            ->with('kilogramme')
            ->will(self::throwException(new UnitNotFound('kilogramme')))
        ;

        $unitRepository->expects(self::never())
            ->method('exists')
            ->willReturn(true)
        ;

        $unitRepository->expects(self::never())
            ->method('changeLabel')
            ->with($unit)
        ;

        // Act
        $this->expectException(UnitNotFound::class);
        $this->expectExceptionMessage(UnitNotFound::MESSAGE);
        $useCase->execute($request);
    }
}
