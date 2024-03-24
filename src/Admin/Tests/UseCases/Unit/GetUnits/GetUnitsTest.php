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

namespace Admin\Tests\UseCases\Unit\GetUnits;

use Admin\Entities\Unit\UnitCollection;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use Admin\UseCases\Gateway\UnitRepository;
use Admin\UseCases\Unit\GetUnits\GetUnits;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 */
final class GetUnitsTest extends TestCase
{
    public function testGetUnitsWillSucceed(): void
    {
        // Arrange
        $unitRepository = $this->createMock(UnitRepository::class);
        $unitBuilder = new UnitDataBuilder();
        $unit1 = $unitBuilder->create('Kilogramme', 'kg')->build();
        $unit2 = $unitBuilder->create('Litre', 'L')->build();
        $units = new UnitCollection();
        $units->add($unit1);
        $units->add($unit2);

        $unitRepository->expects(self::once())->method('findAllUnits')->willReturn($units);

        $useCase = new GetUnits($unitRepository);

        // Act
        $response = $useCase->execute();
        $getUnits = $response->units;

        // Assert
        self::assertCount(2, $getUnits);
        $getUnit1 = $getUnits->current();
        self::assertSame('Kilogramme', $getUnit1->label()->toString());
        self::assertSame('kg', $getUnit1->abbreviation());
        self::assertSame('kilogramme', $getUnit1->slug());
        $getUnits->next();
        $getUnit2 = $getUnits->current();
        self::assertSame('Litre', $getUnit2->label()->toString());
        self::assertSame('L', $getUnit2->abbreviation());
        self::assertSame('litre', $getUnit2->slug());
    }
}
