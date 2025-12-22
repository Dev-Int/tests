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

    public function testComputeQuantity(): void
    {
        // Arrange
        $amount = Amount::fromCents(1000);
        $quantity = Quantity::fromUnit(5.0);

        // Act
        $result = $amount->computeQuantity($quantity);

        // Assert
        self::assertSame(expected: 5000, actual: $result->toInt());
    }
}
