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

use Inventory\Entities\VO\StockDifference;
use PHPUnit\Framework\TestCase;
use Shared\Entities\VO\Quantity;

/**
 * @group unitTest
 *
 * @covers \Inventory\Entities\VO\StockDifference
 */
final class StockDifferenceTest extends TestCase
{
    public function testCalculatesPositiveDifferenceWhenRealStockGreaterThanTheoretical(): void
    {
        // Arrange
        $realStock = Quantity::fromUnit(12.0);
        $theoreticalStock = Quantity::fromUnit(10.0);

        // Act
        $difference = StockDifference::calculate($realStock, $theoreticalStock);

        // Assert
        self::assertSame(2000, $difference->toMilliemes());
        self::assertSame(2.0, $difference->toUnit());
        self::assertTrue($difference->isPositive());
        self::assertFalse($difference->isNegative());
        self::assertFalse($difference->isZero());
    }

    public function testCalculatesNegativeDifferenceWhenRealStockLessThanTheoretical(): void
    {
        // Arrange
        $realStock = Quantity::fromUnit(8.5);
        $theoreticalStock = Quantity::fromUnit(10.0);

        // Act
        $difference = StockDifference::calculate($realStock, $theoreticalStock);

        // Assert
        self::assertSame(-1500, $difference->toMilliemes());
        self::assertSame(-1.5, $difference->toUnit());
        self::assertFalse($difference->isPositive());
        self::assertTrue($difference->isNegative());
        self::assertFalse($difference->isZero());
    }

    public function testCalculatesZeroDifferenceWhenStocksAreEqual(): void
    {
        // Arrange
        $realStock = Quantity::fromUnit(10.0);
        $theoreticalStock = Quantity::fromUnit(10.0);

        // Act
        $difference = StockDifference::calculate($realStock, $theoreticalStock);

        // Assert
        self::assertSame(0, $difference->toMilliemes());
        self::assertSame(0.0, $difference->toUnit());
        self::assertFalse($difference->isPositive());
        self::assertFalse($difference->isNegative());
        self::assertTrue($difference->isZero());
    }

    public function testHandlesDecimalPrecision(): void
    {
        // Arrange
        $realStock = Quantity::fromUnit(12.345);
        $theoreticalStock = Quantity::fromUnit(10.123);

        // Act
        $difference = StockDifference::calculate($realStock, $theoreticalStock);

        // Assert
        self::assertSame(2222, $difference->toMilliemes());
        self::assertSame(2.222, $difference->toUnit());
    }
}
