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

namespace Inventory\Tests\Entities;

use Inventory\Entities\Exception\InvalidStatusTransition;
use Inventory\Entities\VO\InventoryStatus;
use Inventory\Tests\Factory\InventoryFakerFactory;
use PHPUnit\Framework\TestCase;
use Shared\Entities\Exception\DomainException;

/**
 * @group unitTest
 *
 * @covers \Inventory\Entities\Inventory
 */
final class InventoryTransitionStatusTest extends TestCase
{
    public function testCreatesInventoryWithDraftStatus(): void
    {
        // Arrange & Act
        $inventory = (new InventoryFakerFactory())->createDraft()->build();

        // Assert
        self::assertTrue($inventory->status()->equals(InventoryStatus::DRAFT));
        self::assertEquals(0, $inventory->amount()->toInt());
        self::assertEquals(0, \count($inventory->items()));
    }

    public function testReconstitutesInventoryWithGivenStatus(): void
    {
        // Arrange & Act
        $inventory = (new InventoryFakerFactory())->createInProgress()->build();

        // Assert
        self::assertTrue($inventory->status()->equals(InventoryStatus::IN_PROGRESS));
        self::assertEquals(1000, $inventory->amount()->toInt());
    }

    public function testTransitionsFromDraftToInProgress(): void
    {
        // Arrange
        $inventory = (new InventoryFakerFactory())->createDraft()->build();

        // Act
        $inventory->startProcessing();

        // Assert
        self::assertTrue($inventory->status()->equals(InventoryStatus::IN_PROGRESS));
    }

    public function testTransitionsFromInProgressToReview(): void
    {
        // Arrange
        $inventory = (new InventoryFakerFactory())->createInProgress()->build();

        // Act
        $inventory->submitForReview();

        // Assert
        self::assertTrue($inventory->status()->equals(InventoryStatus::REVIEW));
    }

    public function testTransitionsFromReviewToCompleted(): void
    {
        // Arrange
        $inventory = (new InventoryFakerFactory())->createReviewed()->build();

        // Act
        $inventory->complete();

        // Assert
        self::assertTrue($inventory->status()->equals(InventoryStatus::COMPLETED));
    }

    public function testTransitionsFromReviewBackToInProgress(): void
    {
        // Arrange
        $inventory = (new InventoryFakerFactory())->createReviewed()->build();

        // Act
        $inventory->sendBackToProcessing();

        // Assert
        self::assertTrue($inventory->status()->equals(InventoryStatus::IN_PROGRESS));
    }

    public function testThrowsExceptionWhenTransitioningFromDraftToCompleted(): void
    {
        // Arrange
        $inventory = (new InventoryFakerFactory())->createDraft()->build();

        // Act & Assert
        try {
            $inventory->complete();
            self::fail('Expected InvalidStatusTransition exception was not thrown');
        } catch (InvalidStatusTransition $exception) {
            self::assertSame(InvalidStatusTransition::MESSAGE, $exception->getMessage());
            self::assertSame(DomainException::INVALID_ARGUMENT_CODE, $exception->getCode());

            $data = $exception->jsonSerialize();
            self::assertSame(InventoryStatus::DRAFT->value, $data['fromStatus']);
            self::assertSame(InventoryStatus::COMPLETED->value, $data['toStatus']);
        }
    }

    public function testThrowsExceptionWhenTransitioningFromCompletedToStartProcessing(): void
    {
        // Arrange
        $inventory = (new InventoryFakerFactory())->createCompleted()->build();

        // Act & Assert
        try {
            $inventory->startProcessing();
            self::fail('Expected InvalidStatusTransition exception was not thrown');
        } catch (InvalidStatusTransition $exception) {
            self::assertSame(InvalidStatusTransition::MESSAGE, $exception->getMessage());
            self::assertSame(DomainException::INVALID_ARGUMENT_CODE, $exception->getCode());

            $data = $exception->jsonSerialize();
            self::assertSame(InventoryStatus::COMPLETED->value, $data['fromStatus']);
            self::assertSame(InventoryStatus::IN_PROGRESS->value, $data['toStatus']);
        }
    }

    public function testThrowsExceptionWhenTransitioningFromCompletedBackToProcessing(): void
    {
        // Arrange
        $inventory = (new InventoryFakerFactory())->createCompleted()->build();

        // Act & Assert
        try {
            $inventory->sendBackToProcessing();
            self::fail('Expected InvalidStatusTransition exception was not thrown');
        } catch (InvalidStatusTransition $exception) {
            self::assertSame(InvalidStatusTransition::MESSAGE, $exception->getMessage());
            self::assertSame(DomainException::INVALID_ARGUMENT_CODE, $exception->getCode());

            $data = $exception->jsonSerialize();
            self::assertSame(InventoryStatus::COMPLETED->value, $data['fromStatus']);
            self::assertSame(InventoryStatus::IN_PROGRESS->value, $data['toStatus']);
        }
    }

    public function testThrowsExceptionWhenTransitioningFromCompletedToReview(): void
    {
        // Arrange
        $inventory = (new InventoryFakerFactory())->createCompleted()->build();

        // Act & Assert
        try {
            $inventory->submitForReview();
            self::fail('Expected InvalidStatusTransition exception was not thrown');
        } catch (InvalidStatusTransition $exception) {
            self::assertSame(InvalidStatusTransition::MESSAGE, $exception->getMessage());
            self::assertSame(DomainException::INVALID_ARGUMENT_CODE, $exception->getCode());

            $data = $exception->jsonSerialize();
            self::assertSame(InventoryStatus::COMPLETED->value, $data['fromStatus']);
            self::assertSame(InventoryStatus::REVIEW->value, $data['toStatus']);
        }
    }

    public function testThrowsExceptionWhenSubmittingDraftForReview(): void
    {
        // Arrange
        $inventory = (new InventoryFakerFactory())->createDraft()->build();

        // Act & Assert
        try {
            $inventory->submitForReview();
            self::fail('Expected InvalidStatusTransition exception was not thrown');
        } catch (InvalidStatusTransition $exception) {
            self::assertSame(InvalidStatusTransition::MESSAGE, $exception->getMessage());
            self::assertSame(DomainException::INVALID_ARGUMENT_CODE, $exception->getCode());

            $data = $exception->jsonSerialize();
            self::assertSame(InventoryStatus::DRAFT->value, $data['fromStatus']);
            self::assertSame(InventoryStatus::REVIEW->value, $data['toStatus']);
        }
    }
}
