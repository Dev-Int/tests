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

namespace Admin\Tests\UseCases\ZoneStorage\GetZoneStorages;

use Admin\Entities\ZoneStorage\ZoneStorageCollection;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\ZoneStorageDataBuilder;
use Admin\UseCases\Gateway\ZoneStorageRepository;
use Admin\UseCases\ZoneStorage\GetZoneStorages\GetZoneStorages;
use PHPUnit\Framework\TestCase;

final class GetZoneStoragesTest extends TestCase
{
    public function testGetZoneStoragesWillSucceed(): void
    {
        // Arrange
        $zoneStorageRepository = $this->createMock(ZoneStorageRepository::class);
        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')->build();
        $zoneStorageBuilder = new ZoneStorageDataBuilder();
        $zoneStorage1 = $zoneStorageBuilder->create('Réserve négative', $familyLog)->build();
        $zoneStorage2 = $zoneStorageBuilder->create('Réserve positive', $familyLog)->build();
        $zoneStorages = new ZoneStorageCollection();
        $zoneStorages->add($zoneStorage1);
        $zoneStorages->add($zoneStorage2);

        $zoneStorageRepository->expects(self::once())->method('findAllZone')->willReturn($zoneStorages);

        $useCase = new GetZoneStorages($zoneStorageRepository);

        // Act
        $response = $useCase->execute();
        $getZoneStorages = $response->zoneStorages;

        // Assert
        self::assertCount(2, $getZoneStorages);
        $getZoneStorage1 = $getZoneStorages->current();
        self::assertSame('Réserve négative', $getZoneStorage1->label()->toString());
        self::assertSame('reserve-negative', $getZoneStorage1->slug());
        $getZoneStorages->next();
        $getZoneStorage2 = $getZoneStorages->current();
        self::assertSame('Réserve positive', $getZoneStorage2->label()->toString());
        self::assertSame('reserve-positive', $getZoneStorage2->slug());
    }
}
