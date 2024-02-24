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

namespace Admin\Tests\UseCases\FamilyLog\AssignParentFamilyLog;

use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\UseCases\FamilyLog\ChangeParentFamilyLog\AssignParentFamilyLog;
use Admin\UseCases\FamilyLog\ChangeParentFamilyLog\AssignParentFamilyLogRequest;
use Admin\UseCases\Gateway\FamilyLogRepository;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\once;

final class AssignParentFamilyLogTest extends TestCase
{
    public function testAssignParentFamilyLogSucceedWithoutParentWithoutChildren(): void
    {
        // Arrange
        $repository = $this->createMock(FamilyLogRepository::class);
        $useCase = new AssignParentFamilyLog($repository);
        $familyLogBuilder = new FamilyLogDataBuilder();
        $parent = $familyLogBuilder->create('Surgelé')->build();
        $familyLog = $familyLogBuilder->create('Viande')->build();

        $request = $this->createMock(AssignParentFamilyLogRequest::class);
        $request->expects(self::once())->method('slug')->willReturn('viande');
        $request->expects(self::exactly(2))->method('parent')->willReturn($parent);

        $repository->expects(self::once())
            ->method('findBySlug')
            ->with('viande')
            ->willReturn($familyLog)
        ;

        $repository->expects(once())
            ->method('exists')
            ->with('Viande', $parent)
            ->willReturn(false)
        ;

        $familyLogAssigned = $familyLogBuilder->create('Viande')->withParent($parent)->build();
        $repository->expects(self::once())
            ->method('assignParent')
            ->with($familyLogAssigned)
        ;

        // Act
        $response = $useCase->execute($request);

        // Assert
        self::assertSame($parent, $response->familyLog->parent());
        self::assertSame('Viande', $response->familyLog->label()->toString());
        // @todo à faire évoluer après l'implémentation des articles
        self::assertSame('surgele-viande', $response->familyLog->slug());
        self::assertSame('surgele-viande', $response->familyLog->path());
    }

    public function testAssignParentFamilyLogSucceedWithParentWithoutChildren(): void
    {
        // Arrange
        $repository = $this->createMock(FamilyLogRepository::class);
        $useCase = new AssignParentFamilyLog($repository);
        $familyLogBuilder = new FamilyLogDataBuilder();
        $parent = $familyLogBuilder->create('Surgelé')->build();
        $otherParent = $familyLogBuilder->create('Frais')->build();
        $familyLog = $familyLogBuilder->create('Viande')->withParent($parent)->build();

        $request = $this->createMock(AssignParentFamilyLogRequest::class);
        $request->expects(self::once())->method('slug')->willReturn('viande');
        $request->expects(self::exactly(2))->method('parent')->willReturn($otherParent);

        $repository->expects(self::once())
            ->method('findBySlug')
            ->with('viande')
            ->willReturn($familyLog)
        ;

        $repository->expects(once())
            ->method('exists')
            ->with('Viande', $otherParent)
            ->willReturn(false)
        ;

        $familyLogAssigned = $familyLogBuilder->create('Viande')->withParent($otherParent)->build();
        $repository->expects(self::once())
            ->method('assignParent')
            ->with($familyLogAssigned)
        ;

        // Act
        $response = $useCase->execute($request);

        // Assert
        self::assertSame($otherParent, $response->familyLog->parent());
        self::assertSame('Viande', $response->familyLog->label()->toString());
        // @todo à faire évoluer après l'implémentation des articles
        self::assertSame('frais-viande', $response->familyLog->slug());
        self::assertSame('frais-viande', $response->familyLog->path());
        self::assertSame(2, $response->familyLog->level());
    }

    public function testAssignParentFamilyLogSucceedWithoutParentWithChildren(): void
    {
        // Arrange
        $repository = $this->createMock(FamilyLogRepository::class);
        $useCase = new AssignParentFamilyLog($repository);
        $familyLogBuilder = new FamilyLogDataBuilder();
        $parent = $familyLogBuilder->create('Surgelé')->build();
        $familyLog = $familyLogBuilder->create('Viande')->build();
        $familyLogBuilder->create('Poulet')->withParent($familyLog)->build();

        $request = $this->createMock(AssignParentFamilyLogRequest::class);
        $request->expects(self::once())->method('slug')->willReturn('viande');
        $request->expects(self::exactly(2))->method('parent')->willReturn($parent);

        $repository->expects(self::once())
            ->method('findBySlug')
            ->with('viande')
            ->willReturn($familyLog)
        ;

        $repository->expects(once())
            ->method('exists')
            ->with('Viande', $parent)
            ->willReturn(false)
        ;

        $familyLogAssigned = $familyLogBuilder->create('Viande')->withParent($parent)->build();
        $familyLogBuilder->create('Poulet')->withParent($familyLogAssigned)->build();
        $repository->expects(self::once())
            ->method('assignParent')
            ->with($familyLogAssigned)
        ;

        // Act
        $response = $useCase->execute($request);

        // Assert
        self::assertSame($parent, $response->familyLog->parent());
        self::assertSame('Viande', $response->familyLog->label()->toString());
        // @todo à faire évoluer après l'implémentation des articles
        self::assertSame('surgele-viande', $response->familyLog->slug());
        self::assertSame('surgele-viande', $response->familyLog->path());
        self::assertSame(2, $response->familyLog->level());

        $children = $response->familyLog->children();
        if ($children !== null && \count($children) > 0) {
            $childrenChild = $children[0];

            self::assertSame($familyLog, $childrenChild->parent());
            self::assertSame('surgele-viande-poulet', $childrenChild->slug());
            self::assertSame('surgele-viande-poulet', $childrenChild->path());
            self::assertSame(3, $childrenChild->level());
        }
    }

    public function testAssignParentFamilyLogSucceedWithParentWithChildren(): void
    {
        // Arrange
        $repository = $this->createMock(FamilyLogRepository::class);
        $useCase = new AssignParentFamilyLog($repository);
        $familyLogBuilder = new FamilyLogDataBuilder();
        $parent = $familyLogBuilder->create('Surgelé')->build();
        $otherParent = $familyLogBuilder->create('Frais')->build();
        $familyLog = $familyLogBuilder->create('Viande')->withParent($parent)->build();
        $familyLogBuilder->create('Poulet')->withParent($familyLog)->build();

        $request = $this->createMock(AssignParentFamilyLogRequest::class);
        $request->expects(self::once())->method('slug')->willReturn('viande');
        $request->expects(self::exactly(2))->method('parent')->willReturn($otherParent);

        $repository->expects(self::once())
            ->method('findBySlug')
            ->with('viande')
            ->willReturn($familyLog)
        ;

        $repository->expects(once())
            ->method('exists')
            ->with('Viande', $otherParent)
            ->willReturn(false)
        ;

        $familyLogAssigned = $familyLogBuilder->create('Viande')->withParent($otherParent)->build();
        $familyLogBuilder->create('Poulet')->withParent($familyLogAssigned)->build();
        $repository->expects(self::once())
            ->method('assignParent')
            ->with($familyLogAssigned)
        ;

        // Act
        $response = $useCase->execute($request);

        // Assert
        self::assertSame($otherParent, $response->familyLog->parent());
        self::assertSame('Viande', $response->familyLog->label()->toString());
        // @todo à faire évoluer après l'implémentation des articles
        self::assertSame('frais-viande', $response->familyLog->slug());
        self::assertSame('frais-viande', $response->familyLog->path());
        self::assertSame(2, $response->familyLog->level());

        $children = $response->familyLog->children();
        if ($children !== null && \count($children) > 0) {
            $childrenChild = $children[0];

            self::assertSame($familyLog, $childrenChild->parent());
            self::assertSame('frais-viande-poulet', $childrenChild->slug());
            self::assertSame('frais-viande-poulet', $childrenChild->path());
            self::assertSame(3, $childrenChild->level());
        }
    }
}
