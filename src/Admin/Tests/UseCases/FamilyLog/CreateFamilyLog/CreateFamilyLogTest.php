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

namespace Admin\Tests\UseCases\FamilyLog\CreateFamilyLog;

use Admin\Entities\Exception\FamilyLogAlreadyExistsException;
use Admin\Entities\FamilyLog;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\UseCases\FamilyLog\CreateFamilyLog\CreateFamilyLog;
use Admin\UseCases\FamilyLog\CreateFamilyLog\CreateFamilyLogRequest;
use Admin\UseCases\Gateway\FamilyLogRepository;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 */
final class CreateFamilyLogTest extends TestCase
{
    public function testCreateFamilyLogWithoutParentSucceed(): void
    {
        // Arrange
        $familyLogRepository = $this->createMock(FamilyLogRepository::class);
        $useCase = new CreateFamilyLog($familyLogRepository);
        $request = $this->createMock(CreateFamilyLogRequest::class);

        $request->expects(self::exactly(2))->method('label')->willReturn('Surgelé');
        $request->expects(self::exactly(2))->method('parent')->willReturn(null);

        $familyLogRepository->expects(self::once())
            ->method('exists')
            ->with('Surgelé', null)
            ->willReturn(false)
        ;

        $familyLogRepository->expects(self::once())->method('save');

        // Act
        $response = $useCase->execute($request);
        $familyLog = $response->familyLog;

        // Assert
        self::assertSame('Surgelé', $familyLog->label()->toString());
        self::assertSame('surgele', $familyLog->slug());
        self::assertNull($familyLog->parent());
        self::assertSame('surgele', $familyLog->path());
    }

    public function testCreateFamilyLogWithParentSucceed(): void
    {
        // Arrange
        $familyLogBuilder = new FamilyLogDataBuilder();
        $familyLogRepository = $this->createMock(FamilyLogRepository::class);
        $useCase = new CreateFamilyLog($familyLogRepository);
        $request = $this->createMock(CreateFamilyLogRequest::class);
        $familyLogParent = $familyLogBuilder->create('Surgelé')->build();

        $request->expects(self::exactly(2))->method('label')->willReturn('Viande');
        $request->expects(self::exactly(2))->method('parent')->willReturn($familyLogParent);

        $familyLogRepository->expects(self::once())
            ->method('exists')
            ->with('Viande', $familyLogParent)
            ->willReturn(false)
        ;

        $familyLogRepository->expects(self::once())->method('save');

        // Act
        $response = $useCase->execute($request);
        $familyLog = $response->familyLog;

        // Assert
        self::assertSame('Viande', $familyLog->label()->toString());
        self::assertSame('surgele-viande', $familyLog->slug());
        self::assertInstanceOf(FamilyLog::class, $familyLog->parent());
    }

    public function testCreateFamilyLogWithParentHasParentSucceed(): void
    {
        // Arrange
        $familyLogBuilder = new FamilyLogDataBuilder();
        $familyLogRepository = $this->createMock(FamilyLogRepository::class);
        $useCase = new CreateFamilyLog($familyLogRepository);
        $request = $this->createMock(CreateFamilyLogRequest::class);
        $familyLogGrandParent = $familyLogBuilder->create('Surgelé')->build();
        $familyLogParent = $familyLogBuilder->create('Viande')
            ->withParent($familyLogGrandParent)
            ->build()
        ;

        $request->expects(self::exactly(2))->method('label')->willReturn('Boeuf');
        $request->expects(self::exactly(2))->method('parent')->willReturn($familyLogParent);

        $familyLogRepository->expects(self::once())
            ->method('exists')
            ->with('Boeuf', $familyLogParent)
            ->willReturn(false)
        ;

        $familyLogRepository->expects(self::once())->method('save');

        // Act
        $response = $useCase->execute($request);
        $familyLog = $response->familyLog;

        // Assert
        self::assertSame('Boeuf', $familyLog->label()->toString());
        self::assertSame('surgele-viande-boeuf', $familyLog->slug());
        self::assertInstanceOf(FamilyLog::class, $familyLog->parent());
        self::assertSame('surgele-viande-boeuf', $familyLog->path());
    }

    public function testCreateFamilyLogFailWithAlreadyExistName(): void
    {
        // Arrange
        $familyLogRepository = $this->createMock(FamilyLogRepository::class);
        $useCase = new CreateFamilyLog($familyLogRepository);
        $request = $this->createMock(CreateFamilyLogRequest::class);

        $familyLogRepository->expects(self::once())
            ->method('exists')
            ->with('Surgelé', null)
            ->willReturn(true)
        ;

        $request->expects(self::exactly(2))->method('label')->willReturn('Surgelé');
        $request->expects(self::once())->method('parent')->willReturn(null);

        $familyLogRepository->expects(self::never())->method('save');

        // Act && Assert
        $this->expectException(FamilyLogAlreadyExistsException::class);
        $useCase->execute($request);
    }
}
