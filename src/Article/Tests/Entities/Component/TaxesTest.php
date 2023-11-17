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

namespace Article\Tests\Entities\Component;

use PHPUnit\Framework\TestCase;
use Shared\Entities\VO\Taxes;

/**
 * @group unitTest
 */
final class TaxesTest extends TestCase
{
    public function testInstantiateTaxesFromFloat(): void
    {
        // Arrange & Act
        $taxes = Taxes::fromFloat(0.055);

        // Assert
        self::assertEquals(0.055, $taxes->rate());
        self::assertEquals("5,50\u{a0}%", $taxes->name());
    }

    public function testInstantiateTaxesFromPercent(): void
    {
        // Arrange & Act
        $taxes = Taxes::fromPercent("5,50\u{a0}%");

        // Assert
        self::assertEquals(0.055, $taxes->rate());
        self::assertEquals("5,50\u{a0}%", $taxes->name());
    }
}
