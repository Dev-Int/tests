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
        $amount = Amount::fromInt(250035);

        // Assert
        self::assertEquals(2500.35, $amount->toFloat());
        self::assertEquals(250035, $amount->toInt());
    }
}
