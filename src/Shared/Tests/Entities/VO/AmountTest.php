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

namespace Shared\Tests\Entities\VO;

use Inventory\Entities\VO\StockDifference;
use PHPUnit\Framework\TestCase;
use Shared\Entities\VO\Amount;
use Shared\Entities\VO\Quantity;

/**
 * @group unitTest
 */
final class AmountTest extends TestCase
{
    public function testInstantiateAmountFromFloat(): void
    {
        // Arrange && Act
        $amount = Amount::fromFloat(2500.35);

        // Assert
        self::assertEquals(2500.35, $amount->toFloat());
        self::assertEquals(250035, $amount->toInt());
    }

    public function testInstantiateAmountFromInt(): void
    {
        // Arrange && Act
        $amount = Amount::fromCents(250035);

        // Assert
        self::assertEquals(2500.35, $amount->toFloat());
        self::assertEquals(250035, $amount->toInt());
    }

    public function testComputeQuantityWithQuantity(): void
    {
        // Arrange
        $amount = Amount::fromCents(1000); // 10.00€
        $quantity = Quantity::fromUnit(5.0);

        // Act
        $result = $amount->computeQuantity($quantity);

        // Assert: 10.00€ × 5 = 50.00€
        self::assertSame(expected: 5000, actual: $result->toInt());
    }

    public function testComputeQuantityWithPositiveStockDifference(): void
    {
        // Arrange
        $amount = Amount::fromCents(1000); // 10.00€
        $realStock = Quantity::fromUnit(12.0);
        $theoreticalStock = Quantity::fromUnit(10.0);
        $difference = StockDifference::calculate($realStock, $theoreticalStock); // +2 units (surplus)

        // Act
        $result = $amount->computeQuantity($difference);

        // Assert: 10.00€ × 2 = 20.00€ (positive = gain)
        self::assertSame(2000, $result->toInt());
    }

    public function testComputeQuantityWithNegativeStockDifference(): void
    {
        // Arrange
        $amount = Amount::fromCents(1000); // 10.00€
        $realStock = Quantity::fromUnit(8.0);
        $theoreticalStock = Quantity::fromUnit(10.0);
        $difference = StockDifference::calculate($realStock, $theoreticalStock); // -2 units (shortage)

        // Act
        $result = $amount->computeQuantity($difference);

        // Assert: 10.00€ × -2 = -20.00€ (negative = loss)
        self::assertSame(-2000, $result->toInt());
    }

    public function testComputeQuantityWithZeroStockDifference(): void
    {
        // Arrange
        $amount = Amount::fromCents(1000); // 10.00€
        $realStock = Quantity::fromUnit(10.0);
        $theoreticalStock = Quantity::fromUnit(10.0);
        $difference = StockDifference::calculate($realStock, $theoreticalStock); // 0 units

        // Act
        $result = $amount->computeQuantity($difference);

        // Assert: 10.00€ × 0 = 0.00€
        self::assertSame(0, $result->toInt());
    }

    public function testAddTwoPositiveAmounts(): void
    {
        // Arrange
        $amount1 = Amount::fromCents(1000); // 10.00€
        $amount2 = Amount::fromCents(500);  // 5.00€

        // Act
        $result = $amount1->add($amount2);

        // Assert: 10.00€ + 5.00€ = 15.00€
        self::assertSame(1500, $result->toInt());
    }

    public function testAddPositiveAndNegativeAmounts(): void
    {
        // Arrange
        $gain = Amount::fromCents(2000);   // +20.00€ (surplus)
        $loss = Amount::fromCents(-1500);  // -15.00€ (shortage)

        // Act
        $result = $gain->add($loss);

        // Assert: 20.00€ + (-15.00€) = 5.00€ net gain
        self::assertSame(500, $result->toInt());
    }

    public function testAddToZeroAmount(): void
    {
        // Arrange
        $zero = Amount::zero();
        $amount = Amount::fromCents(1000);

        // Act
        $result = $zero->add($amount);

        // Assert
        self::assertSame(1000, $result->toInt());
    }

    public function testAddIsImmutable(): void
    {
        // Arrange
        $original = Amount::fromCents(1000);
        $toAdd = Amount::fromCents(500);

        // Act
        $result = $original->add($toAdd);

        // Assert: original unchanged (immutability)
        self::assertSame(1000, $original->toInt());
        self::assertSame(1500, $result->toInt());
    }
}
