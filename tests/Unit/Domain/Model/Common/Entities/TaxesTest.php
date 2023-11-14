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

namespace Tests\Unit\Domain\Model\Common\Entities;

use PHPUnit\Framework\TestCase;
use Shared\Entities\VO\Taxes;

class TaxesTest extends TestCase
{
    final public function testInstantiateTaxesFromFloat(): void
    {
        // Arrange & Act
        $taxes = Taxes::fromFloat(0.055);

        // Assert
        static::assertEquals(0.055, $taxes->rate());
        static::assertEquals("5,50\u{a0}%", $taxes->name());
    }

    final public function testInstantiateTaxesFromPercent(): void
    {
        // Arrange & Act
        $taxes = Taxes::fromPercent("5,50\u{a0}%");

        // Assert
        static::assertEquals(0.055, $taxes->rate());
        static::assertEquals("5,50\u{a0}%", $taxes->name());
    }
}
