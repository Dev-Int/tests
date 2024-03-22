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

namespace Admin\Tests\UseCases\ZoneStorage\CreateZoneStorage;

use Admin\Entities\Exception\FamilyLogNotFoundException;
use Admin\Entities\Exception\ZoneStorageAlreadyExistsException;
use Admin\Entities\FamilyLog;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\UseCases\Gateway\ZoneStorageRepository;
use Admin\UseCases\ZoneStorage\CreateZoneStorage\CreateZoneStorage;
use Admin\UseCases\ZoneStorage\CreateZoneStorage\CreateZoneStorageRequest;
use PHPUnit\Framework\TestCase;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\NameField;

final class CreateZoneStorageTest extends TestCase
{
    public function testCreateZoneStorageSucceed(): void
    {
        // Arrange
        $zoneStorageRepository = $this->createMock(ZoneStorageRepository::class);
        $useCase = new CreateZoneStorage($zoneStorageRepository);
        $request = $this->createMock(CreateZoneStorageRequest::class);
        $familyLog = FamilyLog::create(ResourceUuid::generate(), NameField::fromString('Surgelé'));

        $request->expects(self::exactly(2))->method('label')->willReturn('Réserve négative');
        $request->expects(self::once())->method('familyLog')->willReturn($familyLog);

        $zoneStorageRepository->expects(self::once())
            ->method('exists')
            ->with('Réserve négative')
            ->willReturn(false)
        ;

        $zoneStorageRepository->expects(self::once())->method('save');

        // Act
        $response = $useCase->execute($request);
        $zoneStorage = $response->zoneStorage;

        // Assert
        self::assertSame('Réserve négative', $zoneStorage->label()->toString());
        self::assertSame('reserve-negative', $zoneStorage->slug());
        self::assertSame($familyLog, $zoneStorage->familyLog());
    }

    public function testCreateZoneStorageFailWithAlreadyExistsException(): void
    {
        // Arrange
        $zoneStorageRepository = $this->createMock(ZoneStorageRepository::class);
        $useCase = new CreateZoneStorage($zoneStorageRepository);
        $request = $this->createMock(CreateZoneStorageRequest::class);
        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')->build();

        $request->expects(self::exactly(2))->method('label')->willReturn('Réserve négative');
        $request->expects(self::never())->method('familyLog')->willReturn($familyLog);

        $zoneStorageRepository->expects(self::once())
            ->method('exists')
            ->with('Réserve négative')
            ->willReturn(true)
        ;

        $zoneStorageRepository->expects(self::never())->method('save');

        // Act && Assert
        $this->expectException(ZoneStorageAlreadyExistsException::class);
        $this->expectExceptionMessage(ZoneStorageAlreadyExistsException::MESSAGE);
        $useCase->execute($request);
    }

    public function testCreateZoneStorageFailWithFamilyLogNotFoundException(): void
    {
        // Arrange
        $zoneStorageRepository = $this->createMock(ZoneStorageRepository::class);
        $useCase = new CreateZoneStorage($zoneStorageRepository);
        $request = $this->createMock(CreateZoneStorageRequest::class);
        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')->build();

        $request->expects(self::exactly(2))->method('label')->willReturn('Réserve négative');
        $request->expects(self::once())->method('familyLog')->willReturn($familyLog);

        $zoneStorageRepository->expects(self::once())
            ->method('exists')
            ->with('Réserve négative')
            ->willReturn(false)
        ;

        // Act && Assert
        $this->expectException(FamilyLogNotFoundException::class);
        $this->expectExceptionMessage(FamilyLogNotFoundException::MESSAGE);

        $zoneStorageRepository->expects(self::once())
            ->method('save')
            ->will(self::throwException(new FamilyLogNotFoundException($familyLog->slug())))
        ;

        $useCase->execute($request);
    }
}
