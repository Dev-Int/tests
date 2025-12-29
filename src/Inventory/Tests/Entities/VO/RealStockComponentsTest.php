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

namespace Inventory\Tests\Entities\VO;

use Inventory\Entities\VO\RealStockComponents;
use PHPUnit\Framework\TestCase;
use Shared\Entities\Exception\NegativeQuantity;

/**
 * @group unitTest
 *
 * @covers \Inventory\Entities\VO\RealStockComponents
 */
final class RealStockComponentsTest extends TestCase
{
    public function testZeroCreatesAllZeroQuantities(): void
    {
        // Arrange & Act
        $components = RealStockComponents::zero();

        // Assert
        self::assertSame(0, $components->parcel->toMilliemes());
        self::assertSame(0, $components->subPackage->toMilliemes());
        self::assertSame(0, $components->consumerUnit->toMilliemes());
    }

    public function testFromUnitsCreatesCorrectQuantities(): void
    {
        // Arrange & Act
        $components = RealStockComponents::fromUnits(5.5, 3.0, 1.25);

        // Assert
        self::assertSame(5.500, $components->parcel->toUnit());
        self::assertSame(3.000, $components->subPackage->toUnit());
        self::assertSame(1.250, $components->consumerUnit->toUnit());
    }

    public function testFromMilliemesCreatesCorrectQuantities(): void
    {
        // Arrange & Act
        $components = RealStockComponents::fromMilliemes(5500, 3000, 1250);

        // Assert
        self::assertSame(5500, $components->parcel->toMilliemes());
        self::assertSame(3000, $components->subPackage->toMilliemes());
        self::assertSame(1250, $components->consumerUnit->toMilliemes());
    }

    public function testConversionRoundTrip(): void
    {
        // Arrange
        $originalParcel = 5.5;
        $originalSubPackage = 3.0;
        $originalConsumerUnit = 1.25;

        // Act - units → milliemes → units
        $fromUnits = RealStockComponents::fromUnits($originalParcel, $originalSubPackage, $originalConsumerUnit);
        $fromMilliemes = RealStockComponents::fromMilliemes(
            $fromUnits->parcel->toMilliemes(),
            $fromUnits->subPackage->toMilliemes(),
            $fromUnits->consumerUnit->toMilliemes(),
        );

        // Assert
        self::assertSame($originalParcel, $fromMilliemes->parcel->toUnit());
        self::assertSame($originalSubPackage, $fromMilliemes->subPackage->toUnit());
        self::assertSame($originalConsumerUnit, $fromMilliemes->consumerUnit->toUnit());
    }

    public function testDecimalPrecisionTruncatesToThreeDecimals(): void
    {
        // Arrange & Act - more than 3 decimals
        $components = RealStockComponents::fromUnits(1.2345678, 2.9999999, 0.0001111);

        // Assert - truncated to 3 decimals
        self::assertSame(1.234, $components->parcel->toUnit());
        self::assertSame(2.999, $components->subPackage->toUnit());
        self::assertSame(0.000, $components->consumerUnit->toUnit());
    }

    public function testNegativeParcelThrowsException(): void
    {
        // Assert
        $this->expectException(NegativeQuantity::class);

        // Arrange & Act
        RealStockComponents::fromUnits(-5.0, 0, 0);
    }

    public function testNegativeSubPackageThrowsException(): void
    {
        // Assert
        $this->expectException(NegativeQuantity::class);

        // Arrange & Act
        RealStockComponents::fromUnits(0, -3.0, 0);
    }

    public function testNegativeConsumerUnitThrowsException(): void
    {
        // Assert
        $this->expectException(NegativeQuantity::class);

        // Arrange & Act
        RealStockComponents::fromUnits(0, 0, -1.0);
    }
}
