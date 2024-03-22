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

namespace Admin\Tests\UseCases\ZoneStorage\ChangeZoneStorageFamilyLog;

use Admin\Entities\Exception\FamilyLogNotFoundException;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\ZoneStorageDataBuilder;
use Admin\UseCases\Gateway\ZoneStorageRepository;
use Admin\UseCases\ZoneStorage\ChangeZoneStorageFamilyLog\ChangeZoneStorageFamilyLog;
use Admin\UseCases\ZoneStorage\ChangeZoneStorageFamilyLog\ChangeZoneStorageFamilyLogRequest;
use PHPUnit\Framework\TestCase;

final class ChangeZoneStorageFamilyLogTest extends TestCase
{
    public function testChangeZoneStorageFamilyLogWillSucceed(): void
    {
        // Arrange
        $zoneStorageRepository = $this->createMock(ZoneStorageRepository::class);
        $useCase = new ChangeZoneStorageFamilyLog($zoneStorageRepository);
        $request = $this->createMock(ChangeZoneStorageFamilyLogRequest::class);
        $familyLog1 = (new FamilyLogDataBuilder())->create('Surgelé')->build();
        $familyLog2 = (new FamilyLogDataBuilder())->create('Frais')
            ->withUuid('99282a8d-f344-456c-bbd3-37fe89f3876c')
            ->build()
        ;
        $zoneStorage = (new ZoneStorageDataBuilder())->create('Réserve négative', $familyLog1)->build();

        $request->expects(self::once())->method('slug')->willReturn('reserve-negative');
        $request->expects(self::once())->method('familyLog')->willReturn($familyLog2);

        $zoneStorageRepository->expects(self::once())
            ->method('findBySlug')
            ->with('reserve-negative')
            ->willReturn($zoneStorage)
        ;
        $zoneStorageRepository->expects(self::once())
            ->method('changeFamilyLog')
            ->with($zoneStorage)
        ;

        // Act
        $response = $useCase->execute($request);
        $zoneStorageUpdated = $response->zoneStorage;

        // Assert
        self::assertSame('Réserve négative', $zoneStorageUpdated->label()->toString());
        self::assertSame('reserve-negative', $zoneStorageUpdated->slug());
        self::assertSame($familyLog2, $zoneStorageUpdated->familyLog());
    }

    public function testChangeZoneStorageFamilyLogFailWithFamilyLogNotFoundException(): void
    {
        // Arrange
        $zoneStorageRepository = $this->createMock(ZoneStorageRepository::class);
        $useCase = new ChangeZoneStorageFamilyLog($zoneStorageRepository);
        $request = $this->createMock(ChangeZoneStorageFamilyLogRequest::class);
        $familyLog1 = (new FamilyLogDataBuilder())->create('Surgelé')->build();
        $familyLog2 = (new FamilyLogDataBuilder())->create('Frais')
            ->withUuid('99282a8d-f344-456c-bbd3-37fe89f3876c')
            ->build()
        ;
        $zoneStorage = (new ZoneStorageDataBuilder())->create('Réserve négative', $familyLog1)->build();

        $request->expects(self::once())->method('slug')->willReturn('reserve-negative');
        $request->expects(self::once())->method('familyLog')->willReturn($familyLog2);

        $zoneStorageRepository->expects(self::once())
            ->method('findBySlug')
            ->with('reserve-negative')
            ->willReturn($zoneStorage)
        ;

        // Act && Assert
        $this->expectException(FamilyLogNotFoundException::class);

        $zoneStorageRepository->expects(self::once())
            ->method('changeFamilyLog')
            ->with($zoneStorage)
            ->will(self::throwException(new FamilyLogNotFoundException($familyLog2->slug())))
        ;
        $useCase->execute($request);
    }
}
