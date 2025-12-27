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

namespace Inventory\Tests\Entities\Exception;

use Inventory\Entities\Exception\IncompleteInventoryCounting;
use PHPUnit\Framework\TestCase;
use Shared\Entities\Exception\DomainException;
use Shared\Entities\ResourceUuid;

/**
 * @group unitTest
 *
 * @covers \Inventory\Entities\Exception\IncompleteInventoryCounting
 */
final class IncompleteInventoryCountingTest extends TestCase
{
    public function testExceptionHasCorrectMessage(): void
    {
        // Arrange
        $zone1 = ResourceUuid::generate();
        $zone2 = ResourceUuid::generate();

        // Act
        $exception = new IncompleteInventoryCounting(zonesWithUncountedItems: [$zone1, $zone2]);

        // Assert
        self::assertSame(IncompleteInventoryCounting::MESSAGE, $exception->getMessage());
        self::assertSame(DomainException::INVALID_ARGUMENT_CODE, $exception->getCode());
    }

    public function testExceptionProvidesZonesWithUncountedItems(): void
    {
        // Arrange
        $zone1 = ResourceUuid::generate();
        $zone2 = ResourceUuid::generate();

        // Act
        $exception = new IncompleteInventoryCounting(zonesWithUncountedItems: [$zone1, $zone2]);

        // Assert
        self::assertCount(2, $exception->zonesWithUncountedItems());
        self::assertSame($zone1->toString(), $exception->zonesWithUncountedItems()[0]->toString());
        self::assertSame($zone2->toString(), $exception->zonesWithUncountedItems()[1]->toString());
    }

    public function testExceptionJsonSerializeIncludesZones(): void
    {
        // Arrange
        $zone1 = ResourceUuid::generate();
        $zone2 = ResourceUuid::generate();

        // Act
        $exception = new IncompleteInventoryCounting(zonesWithUncountedItems: [$zone1, $zone2]);
        $json = $exception->jsonSerialize();

        // Assert
        self::assertArrayHasKey('zonesWithUncountedItems', $json);
        self::assertIsArray($json['zonesWithUncountedItems']);

        /** @var array<string> $zones */
        $zones = $json['zonesWithUncountedItems'];
        self::assertCount(2, $zones);
        self::assertContains($zone1->toString(), $zones);
        self::assertContains($zone2->toString(), $zones);
    }

    public function testExceptionJsonCanBeDeserializedCorrectly(): void
    {
        // Arrange
        $zone1 = ResourceUuid::generate();
        $zone2 = ResourceUuid::generate();
        $exception = new IncompleteInventoryCounting(zonesWithUncountedItems: [$zone1, $zone2]);

        // Act - Cycle complet : sérialisation → JSON string → désérialisation
        $jsonString = json_encode($exception, \JSON_THROW_ON_ERROR);
        $decoded = json_decode($jsonString, associative: true, flags: \JSON_THROW_ON_ERROR);

        // Assert
        self::assertIsArray($decoded);
        self::assertSame(IncompleteInventoryCounting::MESSAGE, $decoded['message']);
        self::assertSame(DomainException::INVALID_ARGUMENT_CODE, $decoded['code']);

        self::assertIsArray($decoded['zonesWithUncountedItems']);
        self::assertCount(2, $decoded['zonesWithUncountedItems']);
        self::assertContains($zone1->toString(), $decoded['zonesWithUncountedItems']);
        self::assertContains($zone2->toString(), $decoded['zonesWithUncountedItems']);
    }
}
