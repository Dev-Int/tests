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
        // Order: consumerUnit, subPackage, parcel
        $components = RealStockComponents::fromUnits(5.5, 3.0, 1.25);

        // Assert
        self::assertSame(5.500, $components->consumerUnit->toUnit());
        self::assertSame(3.000, $components->subPackage->toUnit());
        self::assertSame(1.250, $components->parcel->toUnit());
    }

    public function testFromMilliemesCreatesCorrectQuantities(): void
    {
        // Arrange & Act
        // Order: consumerUnit, subPackage, parcel
        $components = RealStockComponents::fromMilliemes(5500, 3000, 1250);

        // Assert
        self::assertSame(5500, $components->consumerUnit->toMilliemes());
        self::assertSame(3000, $components->subPackage->toMilliemes());
        self::assertSame(1250, $components->parcel->toMilliemes());
    }

    public function testConversionRoundTrip(): void
    {
        // Arrange - order: consumerUnit, subPackage, parcel
        $originalConsumerUnit = 5.5;
        $originalSubPackage = 3.0;
        $originalParcel = 1.25;

        // Act - units → milliemes → units
        $fromUnits = RealStockComponents::fromUnits($originalConsumerUnit, $originalSubPackage, $originalParcel);
        $fromMilliemes = RealStockComponents::fromMilliemes(
            $fromUnits->consumerUnit->toMilliemes(),
            $fromUnits->subPackage->toMilliemes(),
            $fromUnits->parcel->toMilliemes(),
        );

        // Assert
        self::assertSame($originalConsumerUnit, $fromMilliemes->consumerUnit->toUnit());
        self::assertSame($originalSubPackage, $fromMilliemes->subPackage->toUnit());
        self::assertSame($originalParcel, $fromMilliemes->parcel->toUnit());
    }

    public function testDecimalPrecisionTruncatesToThreeDecimals(): void
    {
        // Arrange & Act - more than 3 decimals
        // Order: consumerUnit, subPackage, parcel
        $components = RealStockComponents::fromUnits(1.2345678, 2.9999999, 0.0001111);

        // Assert - truncated to 3 decimals
        self::assertSame(1.234, $components->consumerUnit->toUnit());
        self::assertSame(2.999, $components->subPackage->toUnit());
        self::assertSame(0.000, $components->parcel->toUnit());
    }

    public function testNegativeConsumerUnitThrowsException(): void
    {
        // Assert
        $this->expectException(NegativeQuantity::class);

        // Arrange & Act - order: consumerUnit, subPackage, parcel
        RealStockComponents::fromUnits(-5.0, 0, 0);
    }

    public function testNegativeSubPackageThrowsException(): void
    {
        // Assert
        $this->expectException(NegativeQuantity::class);

        // Arrange & Act - order: consumerUnit, subPackage, parcel
        RealStockComponents::fromUnits(0, -3.0, 0);
    }

    public function testNegativeParcelThrowsException(): void
    {
        // Assert
        $this->expectException(NegativeQuantity::class);

        // Arrange & Act - order: consumerUnit, subPackage, parcel
        RealStockComponents::fromUnits(0, 0, -1.0);
    }
}
