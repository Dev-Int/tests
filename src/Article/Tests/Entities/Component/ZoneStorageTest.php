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

use Article\Entities\Component\ZoneStorage;
use PHPUnit\Framework\TestCase;
use Shared\Entities\VO\NameField;

/**
 * @group unitTest
 */
final class ZoneStorageTest extends TestCase
{
    public function testInstantiateZoneStorage(): void
    {
        // Arrange && Act
        $zone = ZoneStorage::create(NameField::fromString('Réserve positive'));

        // Assert
        self::assertSame('Réserve positive', $zone->name()->toString());
        self::assertSame('reserve-positive', $zone->slug());
    }

    public function testRenameZone(): void
    {
        // Arrange
        $zone = ZoneStorage::create(NameField::fromString('Réserve positive'));

        // Act
        $zone->renameZone(NameField::fromString('Réserve négative'));

        // Assert
        self::assertSame('Réserve négative', $zone->name()->toString());
        self::assertSame('reserve-negative', $zone->slug());
    }
}
