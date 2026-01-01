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

namespace Inventory\Tests\Adapters\Controller\Symfony\Controller\ReviewInventory;

use Inventory\Adapters\Controller\Symfony\Controller\ReviewInventory\ReviewInventoryPresenter;
use Inventory\Tests\Factory\InventoryItemFakerFactory;
use PHPUnit\Framework\TestCase;
use Shared\Entities\ResourceUuid;

/**
 * @group unitTest
 *
 * @covers \Inventory\Adapters\Controller\Symfony\Controller\ReviewInventory\ReviewInventoryPresenter
 */
final class ReviewInventoryPresenterTest extends TestCase
{
    public function testGetZonesWithUnreviewedItemsReturnsOnlyUnreviewedZones(): void
    {
        // Arrange
        $zone1 = ResourceUuid::generate();
        $zone2 = ResourceUuid::generate();

        $factory = new InventoryItemFakerFactory();
        $items = [
            $factory->createWithPreciseStocks(10, 8, $zone1)->build(),           // unreviewed, zone1
            $factory->createWithPreciseStocks(10, 12, $zone2)->asReviewed()->build(), // reviewed, zone2
        ];

        $presenter = new ReviewInventoryPresenter($items);

        // Act
        $zones = $presenter->getZonesWithUnreviewedItems();

        // Assert
        self::assertCount(1, $zones);
        self::assertContains($zone1->toString(), $zones);
        self::assertNotContains($zone2->toString(), $zones);
    }

    public function testGetZonesWithUnreviewedItemsReturnsUniqueZones(): void
    {
        // Arrange
        $zone1 = ResourceUuid::generate();

        $factory = new InventoryItemFakerFactory();
        $items = [
            $factory->createWithPreciseStocks(10, 8, $zone1)->build(),
            $factory->createWithPreciseStocks(10, 6, $zone1)->build(), // same zone, different item
        ];

        $presenter = new ReviewInventoryPresenter($items);

        // Act
        $zones = $presenter->getZonesWithUnreviewedItems();

        // Assert
        self::assertCount(1, $zones);
        self::assertSame($zone1->toString(), $zones[0]);
    }

    public function testGetZonesWithUnreviewedItemsReturnsEmptyWhenAllReviewed(): void
    {
        // Arrange
        $zone1 = ResourceUuid::generate();

        $factory = new InventoryItemFakerFactory();
        $items = [
            $factory->createWithPreciseStocks(10, 8, $zone1)->asReviewed()->build(),
        ];

        $presenter = new ReviewInventoryPresenter($items);

        // Act
        $zones = $presenter->getZonesWithUnreviewedItems();

        // Assert
        self::assertCount(0, $zones);
    }

    public function testGetZonesWithUnreviewedItemsReturnsEmptyWhenNoItems(): void
    {
        // Arrange
        $presenter = new ReviewInventoryPresenter([]);

        // Act
        $zones = $presenter->getZonesWithUnreviewedItems();

        // Assert
        self::assertCount(0, $zones);
    }
}
