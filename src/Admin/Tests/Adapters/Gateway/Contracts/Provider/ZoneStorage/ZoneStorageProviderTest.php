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

namespace Admin\Tests\Adapters\Gateway\Contracts\Provider\ZoneStorage;

use Admin\Adapters\Gateway\Contracts\Provider\ZoneStorage\ZoneStorageProvider;
use Admin\Contracts\Services\Provider\Exception\ZoneStorageNotFound;
use Admin\Entities\ZoneStorage\ZoneStorage;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\ZoneStorageDataBuilder;
use Admin\UseCases\Gateway\Finder\ZoneStorageFinder;
use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Shared\Entities\ResourceUuid;

/**
 * @group unitTest
 *
 * @covers \Admin\Adapters\Gateway\Contracts\Provider\ZoneStorage\ZoneStorageProvider
 */
final class ZoneStorageProviderTest extends TestCase
{
    public function testProvideReturnsZoneStorageResultWhenZoneExists(): void
    {
        // Arrange
        $uuid = ResourceUuid::fromString(ZoneStorageDataBuilder::VALID_UUID);
        $familyLog = (new FamilyLogDataBuilder())->create(label: 'Frais')->build();
        $zoneStorage = (new ZoneStorageDataBuilder())
            ->create(label: 'Réserve positive', familyLog: $familyLog)
            ->build()
        ;

        $finder = $this->createMock(ZoneStorageFinder::class);
        $finder->expects(self::once())
            ->method('findByUuid')
            ->with($uuid)
            ->willReturn($zoneStorage)
        ;

        $provider = new ZoneStorageProvider($finder);

        // Act
        $result = $provider->provide($uuid);

        // Assert
        self::assertSame($uuid->toString(), $result->uuid->toString());
        self::assertSame('Réserve positive', $result->label->toString());
        self::assertSame('reserve-positive', $result->slug);
    }

    public function testProvideThrowsZoneStorageNotFoundWhenFinderReturnsNull(): void
    {
        // Arrange
        $faker = Factory::create();
        $uuid = ResourceUuid::fromString($faker->uuid());

        $finder = $this->createMock(ZoneStorageFinder::class);
        $finder->expects(self::once())
            ->method('findByUuid')
            ->with($uuid)
            ->willReturn(null)
        ;

        $provider = new ZoneStorageProvider($finder);

        // Assert
        $this->expectException(ZoneStorageNotFound::class);

        // Act
        $provider->provide($uuid);
    }

    public function testProvideAllReturnsAllZoneStoragesWhenIdsIsNull(): void
    {
        // Arrange
        $faker = Factory::create();
        $uuid1Str = $faker->uuid();
        $uuid2Str = $faker->uuid();

        $familyLog = (new FamilyLogDataBuilder())->create(label: 'Frais')->build();
        $zone1 = (new ZoneStorageDataBuilder())
            ->create(label: 'Réserve positive', familyLog: $familyLog)
            ->withUuid(uuid: $uuid1Str)
            ->build()
        ;
        $zone2 = (new ZoneStorageDataBuilder())
            ->create(label: 'Réserve négative', familyLog: $familyLog)
            ->withUuid(uuid: $uuid2Str)
            ->build()
        ;

        $finder = $this->createMock(ZoneStorageFinder::class);
        $finder->expects(self::once())
            ->method('findAllZoneStorages')
            ->willReturn([$zone1, $zone2])
        ;

        $provider = new ZoneStorageProvider($finder);

        // Act
        $result = $provider->provideAll();

        // Assert
        self::assertCount(2, $result);

        $zones = iterator_to_array($result);
        self::assertSame('Réserve positive', $zones[0]->label->toString());
        self::assertSame('Réserve négative', $zones[1]->label->toString());
    }

    public function testProvideAllReturnsFilteredZoneStoragesWhenIdsProvided(): void
    {
        // Arrange
        $faker = Factory::create();
        $uuid1Str = $faker->uuid();
        $uuid2Str = $faker->uuid();
        $uuid1 = ResourceUuid::fromString($uuid1Str);
        $uuid2 = ResourceUuid::fromString($uuid2Str);

        $familyLog = (new FamilyLogDataBuilder())->create(label: 'Frais')->build();
        $zone1 = (new ZoneStorageDataBuilder())
            ->create(label: 'Réserve positive', familyLog: $familyLog)
            ->withUuid(uuid: $uuid1Str)
            ->build()
        ;
        $zone2 = (new ZoneStorageDataBuilder())
            ->create(label: 'Réserve négative', familyLog: $familyLog)
            ->withUuid(uuid: $uuid2Str)
            ->build()
        ;

        $finder = $this->createMock(ZoneStorageFinder::class);
        $finder->expects(self::exactly(2))
            ->method('findByUuid')
            ->willReturnCallback(
                static function (ResourceUuid $uuid) use ($zone1, $zone2, $uuid1Str, $uuid2Str): ?ZoneStorage {
                    return match ($uuid->toString()) {
                        $uuid1Str => $zone1,
                        $uuid2Str => $zone2,
                        default => null,
                    };
                }
            )
        ;

        $provider = new ZoneStorageProvider($finder);

        // Act
        $result = $provider->provideAll([$uuid1, $uuid2]);

        // Assert
        self::assertCount(2, $result);
    }

    public function testProvideAllThrowsZoneStorageNotFoundWhenInvalidIdProvided(): void
    {
        // Arrange
        $faker = Factory::create();
        $invalidUuid = ResourceUuid::fromString($faker->uuid());

        $finder = $this->createMock(ZoneStorageFinder::class);
        $finder->expects(self::once())
            ->method('findByUuid')
            ->with($invalidUuid)
            ->willReturn(null)
        ;

        $provider = new ZoneStorageProvider($finder);

        // Assert
        $this->expectException(ZoneStorageNotFound::class);

        // Act
        $provider->provideAll([$invalidUuid]);
    }
}
