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

use Inventory\Entities\Exception\EqualOrFutureDateExpected;
use Inventory\Entities\VO\InventoryDate;
use PHPUnit\Framework\TestCase;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\Clock\FrozenClock;

/**
 * @group unitTest
 *
 * @covers \Inventory\Entities\VO\InventoryDate
 */
final class InventoryDateTest extends TestCase
{
    protected function setUp(): void
    {
        ClockFactory::initialize(new FrozenClock(new \DateTimeImmutable('2025-12-01')));
    }

    public function testCreatesWithValidFutureDate(): void
    {
        // Arrange
        $futureDate = ClockFactory::clock()->now()->modify('+1 day');

        // Act
        $inventoryDate = InventoryDate::fromDateTimeImmutable($futureDate);

        // Assert
        self::assertEquals($futureDate, $inventoryDate->toDateTimeImmutable());
    }

    public function testCreatesWithToday(): void
    {
        // Arrange
        $today = ClockFactory::clock()->now();

        // Act
        $inventoryDate = InventoryDate::fromDateTimeImmutable($today);

        // Assert
        self::assertEquals($today, $inventoryDate->toDateTimeImmutable());
    }

    public function testThrowsExceptionForPastDate(): void
    {
        // Arrange
        $pastDate = ClockFactory::clock()->now()->modify('-1 day');
        $this->expectException(EqualOrFutureDateExpected::class);
        $this->expectExceptionMessage(EqualOrFutureDateExpected::MESSAGE);

        // Act & Assert
        InventoryDate::fromDateTimeImmutable($pastDate);
    }

    public function testEqualsReturnsTrueForSameDate(): void
    {
        // Arrange
        $date = ClockFactory::clock()->now();
        $inventoryDate1 = InventoryDate::fromDateTimeImmutable($date);
        $inventoryDate2 = InventoryDate::fromDateTimeImmutable($date);

        // Act & Assert
        self::assertTrue($inventoryDate1->equals($inventoryDate2));
    }

    public function testEqualsReturnsFalseForDifferentDate(): void
    {
        // Arrange
        $date1 = ClockFactory::clock()->now();
        $date2 = ClockFactory::clock()->now()->modify('+1 day');
        $inventoryDate1 = InventoryDate::fromDateTimeImmutable($date1);
        $inventoryDate2 = InventoryDate::fromDateTimeImmutable($date2);

        // Act & Assert
        self::assertFalse($inventoryDate1->equals($inventoryDate2));
    }

    public function testReconstituteAllowsPastDate(): void
    {
        // Arrange - a past date that would fail with fromDateTimeImmutable
        $pastDate = ClockFactory::clock()->now()->modify('-1 year');

        // Act - reconstitute bypasses validation
        $inventoryDate = InventoryDate::reconstitute($pastDate);

        // Assert
        self::assertEquals($pastDate, $inventoryDate->toDateTimeImmutable());
    }
}
