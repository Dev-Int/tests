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
use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\NameField;

/**
 * @group unitTest
 */
final class ChangeLabelFamilyLogTest extends TestCase
{
    public function testChangeLabelFamilyLogSucceed(): void
    {
        // Arrange
        $repository = $this->createMock(FamilyLogRepository::class);
        $useCase = new ChangeLabelFamilyLog($repository);
        $familyLog = (new FamilyLogDataBuilder())->create('Viande')->build();
        $request = $this->createMock(ChangeLabelFamilyLogRequest::class);

        $request->expects(self::once())->method('uuid')->willReturn(FamilyLogDataBuilder::VALID_UUID);
        $request->expects(self::exactly(2))->method('label')->willReturn('Viandes');

        $repository->expects(self::once())
            ->method('findByUuid')
            ->with(ResourceUuid::fromString(FamilyLogDataBuilder::VALID_UUID))
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
        self::assertSame('viandes', $response->familyLog->slug());
        self::assertNull($response->familyLog->parent());
    }

    public function testChangeLabelFamilyLogWithChildren(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $familyLogBuilder = new FamilyLogDataBuilder();
        $repository = $this->createMock(FamilyLogRepository::class);
        $useCase = new ChangeLabelFamilyLog($repository);
        $request = $this->createMock(ChangeLabelFamilyLogRequest::class);

        $familyLogParent = $familyLogBuilder->create('Alimentaire')->build();
        $familyLog = $familyLogBuilder->create('Viande')
            ->withUuid($faker->uuid())
            ->withParent($familyLogParent)
            ->build()
        ;
        $familyLogBuilder->create('Boeuf')
            ->withUuid($faker->uuid())
            ->withParent($familyLog)
            ->build()
        ;

        $request->expects(self::once())->method('uuid')->willReturn(FamilyLogDataBuilder::VALID_UUID);
        $request->expects(self::exactly(2))->method('label')->willReturn('Alimentaires');

        $repository->expects(self::once())
            ->method('findByUuid')
            ->with(ResourceUuid::fromString(FamilyLogDataBuilder::VALID_UUID))
            ->willReturn($familyLogParent)
        ;
        $repository->expects(self::once())
            ->method('exists')
            ->with('Alimentaires', $familyLogParent->parent())
            ->willReturn(false)
        ;
        $familyLogParent->changeLabel(NameField::fromString('Alimentaires'));
        $repository->expects(self::once())
            ->method('updateLabel')
            ->with($familyLogParent)
        ;

        // Act
        $response = $useCase->execute($request);

        // Assert
        self::assertSame('Alimentaires', $response->familyLog->label()->toString());
        self::assertSame('alimentaires', $response->familyLog->slug());
        self::assertNotNull($response->familyLog->children());
        $children = $response->familyLog->children();
        $child = $children[0];
        self::assertSame('alimentaires_viande', $child->slug());
        self::assertNull($response->familyLog->parent());
        self::assertNotNull($child->children());
        $grandChildren = $child->children();
        $grandChild = $grandChildren[0];
        self::assertSame('alimentaires_viande_boeuf', $grandChild->slug());
    }

    public function testChangeLabelFamilyLogFailWithAlreadyExistsException(): void
    {
        // Arrange
        $repository = $this->createMock(FamilyLogRepository::class);
        $useCase = new ChangeLabelFamilyLog($repository);
        $familyLog = (new FamilyLogDataBuilder())->create('Viande')->build();
        $request = $this->createMock(ChangeLabelFamilyLogRequest::class);
        $request->expects(self::once())->method('uuid')->willReturn(FamilyLogDataBuilder::VALID_UUID);
        $request->expects(self::exactly(2))->method('label')->willReturn('Viandes');

        $repository->expects(self::once())
            ->method('findByUuid')
            ->with(ResourceUuid::fromString(FamilyLogDataBuilder::VALID_UUID))
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
        $useCase->execute($request);
    }
}
