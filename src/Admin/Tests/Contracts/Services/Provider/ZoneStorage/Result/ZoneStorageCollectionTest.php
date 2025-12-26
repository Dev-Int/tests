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

namespace Admin\Tests\Contracts\Services\Provider\ZoneStorage\Result;

use Admin\Contracts\Services\Provider\ZoneStorage\Result\ZoneStorage;
use Admin\Contracts\Services\Provider\ZoneStorage\Result\ZoneStorageCollection;
use PHPUnit\Framework\TestCase;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\NameField;

/**
 * @group unitTest
 *
 * @covers \Admin\Contracts\Services\Provider\ZoneStorage\Result\ZoneStorageCollection
 */
final class ZoneStorageCollectionTest extends TestCase
{
    public function testToSelectReturnsLabelAsKeyAndUuidAsValue(): void
    {
        // Arrange
        $uuid1 = ResourceUuid::fromString('550e8400-e29b-41d4-a716-446655440001');
        $uuid2 = ResourceUuid::fromString('550e8400-e29b-41d4-a716-446655440002');

        $zone1 = new ZoneStorage($uuid1, NameField::fromString('Chambre froide'), 'chambre-froide');
        $zone2 = new ZoneStorage($uuid2, NameField::fromString('Réserve sèche'), 'reserve-seche');

        $collection = new ZoneStorageCollection();
        $collection->add($zone1);
        $collection->add($zone2);

        // Act
        $select = $collection->toSelect();

        // Assert
        self::assertIsArray($select);
        self::assertCount(2, $select);
        self::assertArrayHasKey('Chambre froide', $select);
        self::assertArrayHasKey('Réserve sèche', $select);
        self::assertSame('550e8400-e29b-41d4-a716-446655440001', $select['Chambre froide']);
        self::assertSame('550e8400-e29b-41d4-a716-446655440002', $select['Réserve sèche']);
    }

    public function testToSelectReturnsEmptyArrayWhenCollectionIsEmpty(): void
    {
        // Arrange
        $collection = new ZoneStorageCollection();

        // Act
        $select = $collection->toSelect();

        // Assert
        self::assertIsArray($select);
        self::assertCount(0, $select);
    }

    public function testToSelectWithSingleItem(): void
    {
        // Arrange
        $uuid = ResourceUuid::fromString('550e8400-e29b-41d4-a716-446655440003');
        $zone = new ZoneStorage($uuid, NameField::fromString('Économat'), 'economat');

        $collection = new ZoneStorageCollection();
        $collection->add($zone);

        // Act
        $select = $collection->toSelect();

        // Assert
        self::assertCount(1, $select);
        self::assertSame(['Économat' => '550e8400-e29b-41d4-a716-446655440003'], $select);
    }
}
