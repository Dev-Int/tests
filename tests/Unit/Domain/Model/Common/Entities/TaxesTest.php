<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Model\Common\Entities;

use Domain\Model\Common\Entities\Taxes;
use PHPUnit\Framework\TestCase;

class TaxesTest extends TestCase
{
    final public function testInstantiateTaxesFromFloat(): void
    {
        // Arrange & Act
        $taxes = Taxes::fromFloat(0.055);

        // Assert
        self::assertEquals(new Taxes(0.055), $taxes);
        self::assertEquals(0.055, $taxes->rate());
        self::assertEquals('5,50 %', $taxes->name());
    }

    final public function testInstantiateTaxesFromPercent(): void
    {
        // Arrange & Act
        $taxes = Taxes::fromPercent('5,50 %');

        // Assert
        self::assertEquals(new Taxes(0.055), $taxes);
        self::assertEquals(0.055, $taxes->rate());
        self::assertEquals('5,50 %', $taxes->name());
    }
}
