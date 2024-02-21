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

namespace Admin\Tests\UseCases\FamilyLog\ChangeLabelFamilyLog;

use Admin\Entities\Exception\FamilyLogAlreadyExistsException;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\UseCases\FamilyLog\ChangeLabelFamilyLog\ChangeLabelFamilyLog;
use Admin\UseCases\FamilyLog\ChangeLabelFamilyLog\ChangeLabelFamilyLogRequest;
use Admin\UseCases\Gateway\FamilyLogRepository;
use PHPUnit\Framework\TestCase;
use Shared\Entities\VO\NameField;

final class ChangeLabelFamilyLogTest extends TestCase
{
    public function testChangeLabelFamilyLogSucceed(): void
    {
        // Arrange
        $repository = $this->createMock(FamilyLogRepository::class);
        $useCase = new ChangeLabelFamilyLog($repository);
        $familyLog = (new FamilyLogDataBuilder())->create('Viande')->build();
        $request = $this->createMock(ChangeLabelFamilyLogRequest::class);
        $request->expects(self::once())->method('slug')->willReturn('viande');
        $request->expects(self::exactly(2))->method('label')->willReturn('Viandes');

        $repository->expects(self::once())
            ->method('findBySlug')
            ->with('viande')
            ->willReturn($familyLog)
        ;
        $repository->expects(self::once())
            ->method('exists')
            ->with('Viandes', $familyLog->parent())
            ->willReturn(false)
        ;
        $familyLog->changeLabel(NameField::fromString('Viandes'));
        $repository->expects(self::once())
            ->method('updateLabel')
            ->with($familyLog)
        ;

        // Act
        $response = $useCase->execute($request);

        // Assert
        self::assertSame('Viandes', $response->familyLog->label()->toString());
        self::assertSame('viande', $response->familyLog->slug());
        self::assertNull($response->familyLog->parent());
    }

    public function testChangeLabelFamilyLogFailWithAlreadyExistsException(): void
    {
        // Arrange
        $repository = $this->createMock(FamilyLogRepository::class);
        $useCase = new ChangeLabelFamilyLog($repository);
        $familyLog = (new FamilyLogDataBuilder())->create('Viande')->build();
        $request = $this->createMock(ChangeLabelFamilyLogRequest::class);
        $request->expects(self::once())->method('slug')->willReturn('viande');
        $request->expects(self::exactly(2))->method('label')->willReturn('Viandes');

        $repository->expects(self::once())
            ->method('findBySlug')
            ->with('viande')
            ->willReturn($familyLog)
        ;
        $repository->expects(self::once())
            ->method('exists')
            ->with('Viandes', $familyLog->parent())
            ->willReturn(true)
        ;
        $repository->expects(self::never())
            ->method('updateLabel')
        ;
        // Assert
        $this->expectException(FamilyLogAlreadyExistsException::class);

        // Act
        $response = $useCase->execute($request);
    }
}
