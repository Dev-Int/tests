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

use Inventory\Entities\VO\InventoryStatus;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 *
 * @covers \Inventory\Entities\VO\InventoryStatus
 */
final class InventoryStatusTest extends TestCase
{
    public function testIsResumableReturnsTrueOnlyForReviewStatus(): void
    {
        // Assert - REVIEW is resumable
        self::assertTrue(InventoryStatus::REVIEW->isResumable());

        // Assert - all other statuses are NOT resumable
        self::assertFalse(InventoryStatus::DRAFT->isResumable());
        self::assertFalse(InventoryStatus::IN_PROGRESS->isResumable());
        self::assertFalse(InventoryStatus::COMPLETED->isResumable());
        self::assertFalse(InventoryStatus::CANCELLED->isResumable());
    }

    public function testIsCancellableReturnsTrueForActiveStatuses(): void
    {
        // Assert - ACTIVE_STATUSES are cancellable
        self::assertTrue(InventoryStatus::DRAFT->isCancellable());
        self::assertTrue(InventoryStatus::IN_PROGRESS->isCancellable());
        self::assertTrue(InventoryStatus::REVIEW->isCancellable());

        // Assert - terminal statuses are NOT cancellable
        self::assertFalse(InventoryStatus::COMPLETED->isCancellable());
        self::assertFalse(InventoryStatus::CANCELLED->isCancellable());
    }

    public function testIsCancelledReturnsTrueOnlyForCancelledStatus(): void
    {
        // Assert - CANCELLED is cancelled
        self::assertTrue(InventoryStatus::CANCELLED->isCancelled());

        // Assert - all other statuses are NOT cancelled
        self::assertFalse(InventoryStatus::DRAFT->isCancelled());
        self::assertFalse(InventoryStatus::IN_PROGRESS->isCancelled());
        self::assertFalse(InventoryStatus::REVIEW->isCancelled());
        self::assertFalse(InventoryStatus::COMPLETED->isCancelled());
    }

    public function testEqualsReturnsTrueForSameStatus(): void
    {
        // Assert
        self::assertTrue(InventoryStatus::DRAFT->equals(InventoryStatus::DRAFT));
        self::assertTrue(InventoryStatus::IN_PROGRESS->equals(InventoryStatus::IN_PROGRESS));
        self::assertTrue(InventoryStatus::REVIEW->equals(InventoryStatus::REVIEW));
        self::assertTrue(InventoryStatus::COMPLETED->equals(InventoryStatus::COMPLETED));
        self::assertTrue(InventoryStatus::CANCELLED->equals(InventoryStatus::CANCELLED));
    }

    public function testEqualsReturnsFalseForDifferentStatus(): void
    {
        // Assert
        self::assertFalse(InventoryStatus::DRAFT->equals(InventoryStatus::IN_PROGRESS));
        self::assertFalse(InventoryStatus::IN_PROGRESS->equals(InventoryStatus::REVIEW));
        self::assertFalse(InventoryStatus::REVIEW->equals(InventoryStatus::COMPLETED));
        self::assertFalse(InventoryStatus::COMPLETED->equals(InventoryStatus::CANCELLED));
        self::assertFalse(InventoryStatus::CANCELLED->equals(InventoryStatus::DRAFT));
    }
}
