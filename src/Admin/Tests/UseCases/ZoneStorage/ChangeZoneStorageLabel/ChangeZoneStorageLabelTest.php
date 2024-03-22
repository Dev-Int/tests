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

namespace Admin\Tests\UseCases\ZoneStorage\ChangeZoneStorageLabel;

use Admin\Entities\Exception\ZoneStorageAlreadyExistsException;
use Admin\Entities\Exception\ZoneStorageNotFoundException;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\ZoneStorageDataBuilder;
use Admin\UseCases\Gateway\ZoneStorageRepository;
use Admin\UseCases\ZoneStorage\ChangeZoneStorageLabel\ChangeZoneStorageLabel;
use Admin\UseCases\ZoneStorage\ChangeZoneStorageLabel\ChangeZoneStorageLabelRequest;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\exactly;

final class ChangeZoneStorageLabelTest extends TestCase
{
    public function testChangeZoneStorageLabelWillSucceed(): void
    {
        // Arrange
        $zoneStorageRepository = $this->createMock(ZoneStorageRepository::class);
        $useCase = new ChangeZoneStorageLabel($zoneStorageRepository);
        $request = $this->createMock(ChangeZoneStorageLabelRequest::class);
        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')->build();
        $zoneStorage = (new ZoneStorageDataBuilder())->create('Réserve négative', $familyLog)->build();

        $request->expects(exactly(1))->method('slug')->willReturn('reserve-negative');
        $request->expects(exactly(2))->method('label')->willReturn('Réserve positive');

        $zoneStorageRepository->expects(self::once())
            ->method('exists')
            ->with('Réserve positive')
            ->willReturn(false)
        ;

        $zoneStorageRepository->expects(self::once())
            ->method('findBySlug')
            ->with('reserve-negative')
            ->willReturn($zoneStorage)
        ;

        $zoneStorageRepository->expects(self::once())
            ->method('changeLabel')
            ->with($zoneStorage)
        ;

        // Act
        $response = $useCase->execute($request);
        $zoneStorageUpdated = $response->zoneStorage;

        // Assert
        self::assertSame('Réserve positive', $zoneStorageUpdated->label()->toString());
        self::assertSame('reserve-positive', $zoneStorageUpdated->slug());
        self::assertSame($familyLog, $zoneStorageUpdated->familyLog());
    }

    public function testChangeZoneStorageLabelFailWithAlreadyExistsException(): void
    {
        // Arrange
        $zoneStorageRepository = $this->createMock(ZoneStorageRepository::class);
        $useCase = new ChangeZoneStorageLabel($zoneStorageRepository);
        $request = $this->createMock(ChangeZoneStorageLabelRequest::class);
        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')->build();
        $zoneStorage = (new ZoneStorageDataBuilder())->create('Réserve négative', $familyLog)->build();

        $request->expects(self::never())->method('slug')->willReturn('reserve-negative');
        $request->expects(self::exactly(2))->method('label')->willReturn('Réserve positive');

        $zoneStorageRepository->expects(self::once())
            ->method('exists')
            ->with('Réserve positive')
            ->willReturn(true)
        ;

        $zoneStorageRepository->expects(self::never())
            ->method('findBySlug')
        ;

        $zoneStorageRepository->expects(self::never())
            ->method('changeLabel')
            ->with($zoneStorage)
        ;

        // Act && Assert
        $this->expectException(ZoneStorageAlreadyExistsException::class);
        $this->expectExceptionMessage(ZoneStorageAlreadyExistsException::MESSAGE);
        $useCase->execute($request);
    }

    public function testChangeZoneStorageLabelFailWithZoneStorageNotFoundException(): void
    {
        // Arrange
        $zoneStorageRepository = $this->createMock(ZoneStorageRepository::class);
        $useCase = new ChangeZoneStorageLabel($zoneStorageRepository);
        $request = $this->createMock(ChangeZoneStorageLabelRequest::class);
        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')->build();
        $zoneStorage = (new ZoneStorageDataBuilder())->create('Réserve négative', $familyLog)->build();

        $request->expects(self::once())->method('slug')->willReturn('reserve-negative');
        $request->expects(self::once())->method('label')->willReturn('Réserve positive');

        $zoneStorageRepository->expects(self::once())
            ->method('exists')
            ->with('Réserve positive')
            ->willReturn(false)
        ;

        $zoneStorageRepository->expects(self::once())
            ->method('findBySlug')
            ->with('reserve-negative')
            ->will(self::throwException(new ZoneStorageNotFoundException($zoneStorage->slug())))
        ;

        // Act && Assert
        $this->expectException(ZoneStorageNotFoundException::class);
        $this->expectExceptionMessage(ZoneStorageNotFoundException::MESSAGE);

        $zoneStorageRepository->expects(self::never())
            ->method('changeLabel')
            ->with($zoneStorage)
        ;
        $useCase->execute($request);
    }
}
