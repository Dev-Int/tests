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

namespace Admin\Tests\Adapters\Gateway\ORM\Finder;

use Admin\Entities\ZoneStorage\ZoneStorage;
use Admin\Tests\Factory\ZoneStorageFactory;
use Admin\UseCases\Gateway\Finder\ZoneStorageFinder;
use App\Shared\Tests\BaseFunctionalTestCase;
use Faker\Factory;
use Shared\Entities\ResourceUuid;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 *
 * @covers \Admin\Adapters\Gateway\ORM\Finder\DoctrineZoneStorageFinder
 */
final class DoctrineZoneStorageFinderTest extends BaseFunctionalTestCase
{
    use Factories;

    private ZoneStorageFinder $finder;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var ZoneStorageFinder $finder */
        $finder = self::getContainer()->get(ZoneStorageFinder::class);
        $this->finder = $finder;
    }

    public function testFindByUuidReturnsZoneStorageWhenItExists(): void
    {
        // Arrange
        $zoneStorageOrm = ZoneStorageFactory::createOne([
            'label' => 'Réserve positive',
        ]);
        $uuid = $zoneStorageOrm->_real()->uuid();

        // Act
        $result = $this->finder->findByUuid(uuid: $uuid);

        // Assert
        self::assertInstanceOf(ZoneStorage::class, $result);
        self::assertSame($uuid, $result->uuid()->toString());
        self::assertSame('Réserve positive', $result->label()->toString());
    }

    public function testFindByUuidReturnsNullWhenZoneStorageDoesNotExist(): void
    {
        // Arrange
        $faker = Factory::create();
        $nonExistentUuid = ResourceUuid::fromString($faker->uuid());

        // Act
        $result = $this->finder->findByUuid(uuid: $nonExistentUuid);

        // Assert
        self::assertNull($result);
    }

    public function testFindAllZoneStoragesReturnsAllZones(): void
    {
        // Arrange
        ZoneStorageFactory::createOne(['label' => 'Réserve positive']);
        ZoneStorageFactory::createOne(['label' => 'Réserve négative']);
        ZoneStorageFactory::createOne(['label' => 'Réserve sèche']);

        // Act
        $result = iterator_to_array($this->finder->findAllZoneStorages());

        // Assert
        self::assertCount(3, $result);
        self::assertContainsOnlyInstancesOf(ZoneStorage::class, $result);
    }
}
