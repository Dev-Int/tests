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

use PHPUnit\Framework\TestCase;
use Shared\Entities\Exception\NegativeQuantity;
use Shared\Entities\VO\Quantity;

/**
 * @group unitTest
 *
 * @covers \Shared\Entities\VO\Quantity
 */
final class QuantityTest extends TestCase
{
    public function testInstantiateQuantityWithFloat(): void
    {
        // Arrange & Act
        $quantity = Quantity::fromUnit(1.25);

        // Assert
        self::assertSame(1.250, $quantity->toUnit());
        self::assertSame(1250, $quantity->toMilliemes());
    }

    public function testInstantiateQuantityWithMilliemes(): void
    {
        // Arrange & Act
        $quantity = Quantity::fromMilliemes(12345);

        // Assert
        self::assertSame(12.345, $quantity->toUnit());
        self::assertSame(12345, $quantity->toMilliemes());
    }

    public function testInstantiateQuantityWithLargeDecimal(): void
    {
        // Arrange & Act
        $quantity = Quantity::fromUnit(12.345678);

        // Assert
        self::assertSame(12.345, $quantity->toUnit());
        self::assertSame(12345, $quantity->toMilliemes());
    }

    public function testIsEqualQuantityFromAnother(): void
    {
        // Arrange
        $quantity = Quantity::fromUnit(1.25);
        $anotherQuantity = Quantity::fromMilliemes(1250);

        // Act
        $isEqual = $quantity->isEqual($anotherQuantity);

        // Assert
        self::assertTrue($isEqual);
    }

    public function testQuantityThrowNegativeQuantityExceptionFromUnit(): void
    {
        // Assert
        $this->expectException(NegativeQuantity::class);
        $this->expectExceptionMessage(NegativeQuantity::MESSAGE);

        // Arrange && Act
        Quantity::fromUnit(-1.25);
    }

    public function testQuantityThrowNegativeQuantityExceptionFromMilliemes(): void
    {
        // Assert
        $this->expectException(NegativeQuantity::class);
        $this->expectExceptionMessage(NegativeQuantity::MESSAGE);

        // Arrange && Act
        Quantity::fromMilliemes(-1250);
    }
}
