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

namespace Admin\Tests\UseCases\Unit\CreateUnit;

use Admin\Entities\Exception\UnitAlreadyExistsException;
use Admin\UseCases\Gateway\UnitRepository;
use Admin\UseCases\Unit\CreateUnit\CreateUnit;
use Admin\UseCases\Unit\CreateUnit\CreateUnitRequest;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 */
final class CreateUnitTest extends TestCase
{
    public function testCreateUnitSuccess(): void
    {
        // Arrange
        $unitRepository = $this->createMock(UnitRepository::class);
        $useCase = new CreateUnit($unitRepository);
        $request = $this->createMock(CreateUnitRequest::class);

        $request->expects(self::exactly(2))->method('label')->willReturn('Kilogramme');
        $request->expects(self::once())->method('abbreviation')->willReturn('kg');

        $unitRepository->expects(self::once())
            ->method('exists')
            ->willReturn(false)
        ;

        $unitRepository->expects(self::once())->method('save');

        // Act
        $response = $useCase->execute($request);
        $unit = $response->unit;

        // Assert
        self::assertSame('Kilogramme', $unit->label()->toString());
        self::assertSame('kilogramme', $unit->slug());
        self::assertSame('kg', $unit->abbreviation());
    }

    public function testCreateUnitFailWithAlreadyExistsException(): void
    {
        // Arrange
        $unitRepository = $this->createMock(UnitRepository::class);
        $useCase = new CreateUnit($unitRepository);
        $request = $this->createMock(CreateUnitRequest::class);

        $request->expects(self::exactly(2))->method('label')->willReturn('Kilogramme');
        $request->expects(self::never())->method('abbreviation')->willReturn('kg');

        $unitRepository->expects(self::once())
            ->method('exists')
            ->willReturn(true)
        ;

        $unitRepository->expects(self::never())->method('save');

        // Act
        $this->expectException(UnitAlreadyExistsException::class);
        $useCase->execute($request);
    }
}
