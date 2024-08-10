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

use Admin\Entities\Exception\FamilyLogAlreadyExistsException;
use Admin\Entities\Exception\FamilyLogNotFoundException;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\UseCases\FamilyLog\ChangeParentFamilyLog\AssignParentFamilyLog;
use Admin\UseCases\FamilyLog\ChangeParentFamilyLog\AssignParentFamilyLogRequest;
use Admin\UseCases\Gateway\FamilyLogRepository;
use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Shared\Entities\ResourceUuid;

use function PHPUnit\Framework\once;

/**
 * @group unitTest
 */
final class AssignParentFamilyLogTest extends TestCase
{
    public function testAssignParentFamilyLogSucceedWithoutParentWithoutChildren(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $repository = $this->createMock(FamilyLogRepository::class);
        $useCase = new AssignParentFamilyLog($repository);
        $familyLogBuilder = new FamilyLogDataBuilder();
        $parent = $familyLogBuilder->create('Surgelé')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLog = $familyLogBuilder->create('Viande')->build();

        $request = $this->createMock(AssignParentFamilyLogRequest::class);
        $request->expects(self::exactly(2))->method('uuid')->willReturn(FamilyLogDataBuilder::VALID_UUID);
        $request->expects(self::exactly(2))->method('parent')->willReturn($parent);

        $repository->expects(self::once())
            ->method('findByUuid')
            ->with(ResourceUuid::fromString(FamilyLogDataBuilder::VALID_UUID))
            ->willReturn($familyLog)
        ;

        $repository->expects(once())
            ->method('exists')
            ->with('Viande', $parent)
            ->willReturn(false)
        ;

        $repository->expects(self::once())
            ->method('assignParent')
            ->with($familyLog)
        ;

        // Act
        $response = $useCase->execute($request);

        // Assert
        self::assertSame($parent, $response->familyLog->parent());
        self::assertSame('Viande', $response->familyLog->label()->toString());
        // @todo à faire évoluer après l'implémentation des articles
        self::assertSame('surgele_viande', $response->familyLog->slug());
        self::assertSame('surgele_viande', $response->familyLog->path());
    }

    public function testAssignParentFamilyLogSucceedWithParentWithoutChildren(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $repository = $this->createMock(FamilyLogRepository::class);
        $useCase = new AssignParentFamilyLog($repository);
        $familyLogBuilder = new FamilyLogDataBuilder();
        $parent = $familyLogBuilder->create('Surgelé')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $otherParent = $familyLogBuilder->create('Frais')->build();
        $familyLog = $familyLogBuilder->create('Viande')->withParent($parent)->build();

        $request = $this->createMock(AssignParentFamilyLogRequest::class);
        $request->expects(self::exactly(2))->method('uuid')->willReturn(FamilyLogDataBuilder::VALID_UUID);
        $request->expects(self::exactly(2))->method('parent')->willReturn($otherParent);

        $repository->expects(self::once())
            ->method('findByUuid')
            ->with(ResourceUuid::fromString(FamilyLogDataBuilder::VALID_UUID))
            ->willReturn($familyLog)
        ;

        $repository->expects(once())
            ->method('exists')
            ->with('Viande', $otherParent)
            ->willReturn(false)
        ;

        $repository->expects(self::once())
            ->method('assignParent')
            ->with($familyLog)
        ;

        // Act
        $response = $useCase->execute($request);

        // Assert
        self::assertSame($otherParent, $response->familyLog->parent());
        self::assertSame('Viande', $response->familyLog->label()->toString());
        self::assertEmpty($parent->children());
        // @todo à faire évoluer après l'implémentation des articles
        self::assertSame('frais_viande', $response->familyLog->slug());
        self::assertSame('frais_viande', $response->familyLog->path());
        self::assertSame(2, $response->familyLog->level());
    }

    public function testAssignParentFamilyLogSucceedWithoutParentWithChildren(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $repository = $this->createMock(FamilyLogRepository::class);
        $useCase = new AssignParentFamilyLog($repository);
        $familyLogBuilder = new FamilyLogDataBuilder();
        $parent = $familyLogBuilder->create('Surgelé')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLog = $familyLogBuilder->create('Viande')->build();
        $familyLogBuilder->create('Poulet')
            ->withUuid($faker->uuid())
            ->withParent($familyLog)
            ->build()
        ;

        $request = $this->createMock(AssignParentFamilyLogRequest::class);
        $request->expects(self::exactly(2))->method('uuid')->willReturn(FamilyLogDataBuilder::VALID_UUID);
        $request->expects(self::exactly(2))->method('parent')->willReturn($parent);

        $repository->expects(self::once())
            ->method('findByUuid')
            ->with(ResourceUuid::fromString(FamilyLogDataBuilder::VALID_UUID))
            ->willReturn($familyLog)
        ;

        $repository->expects(once())
            ->method('exists')
            ->with('Viande', $parent)
            ->willReturn(false)
        ;

        $repository->expects(self::once())
            ->method('assignParent')
            ->with($familyLog, FamilyLogDataBuilder::VALID_UUID)
        ;

        // Act
        $response = $useCase->execute($request);

        // Assert
        self::assertSame($parent, $response->familyLog->parent());
        self::assertSame('Viande', $response->familyLog->label()->toString());
        // @todo à faire évoluer après l'implémentation des articles
        self::assertSame('surgele_viande', $response->familyLog->slug());
        self::assertSame('surgele_viande', $response->familyLog->path());
        self::assertSame(2, $response->familyLog->level());

        $children = $response->familyLog->children();
        self::assertNotEmpty($children);
        $childrenChild = $children[0];

        self::assertSame($familyLog, $childrenChild->parent());
        self::assertSame('surgele_viande_poulet', $childrenChild->slug());
        self::assertSame('surgele_viande_poulet', $childrenChild->path());
        self::assertSame(3, $childrenChild->level());
    }

    public function testAssignParentFamilyLogSucceedWithParentWithChildren(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $repository = $this->createMock(FamilyLogRepository::class);
        $useCase = new AssignParentFamilyLog($repository);
        $familyLogBuilder = new FamilyLogDataBuilder();
        $parent = $familyLogBuilder->create('Surgelé')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $otherParent = $familyLogBuilder->create('Frais')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLog = $familyLogBuilder->create('Viande')->withParent($parent)->build();
        $familyLogBuilder->create('Poulet')
            ->withParent($familyLog)
            ->withUuid($faker->uuid())
            ->build()
        ;

        $request = $this->createMock(AssignParentFamilyLogRequest::class);
        $request->expects(self::exactly(2))->method('uuid')->willReturn(FamilyLogDataBuilder::VALID_UUID);
        $request->expects(self::exactly(2))->method('parent')->willReturn($otherParent);

        $repository->expects(self::once())
            ->method('findByUuid')
            ->with(ResourceUuid::fromString(FamilyLogDataBuilder::VALID_UUID))
            ->willReturn($familyLog)
        ;

        $repository->expects(once())
            ->method('exists')
            ->with('Viande', $otherParent)
            ->willReturn(false)
        ;

        $repository->expects(self::once())
            ->method('assignParent')
            ->with($familyLog)
        ;

        // Act
        $response = $useCase->execute($request);

        // Assert
        self::assertSame($otherParent, $response->familyLog->parent());
        self::assertSame('Viande', $response->familyLog->label()->toString());
        // @todo à faire évoluer après l'implémentation des articles
        self::assertSame('frais_viande', $response->familyLog->slug());
        self::assertSame('frais_viande', $response->familyLog->path());
        self::assertSame(2, $response->familyLog->level());
        self::assertEmpty($parent->children());

        $children = $response->familyLog->children();
        self::assertNotEmpty($children);
        $childrenChild = $children[0];

        self::assertSame($familyLog, $childrenChild->parent());
        self::assertSame('frais_viande_poulet', $childrenChild->slug());
        self::assertSame('frais_viande_poulet', $childrenChild->path());
        self::assertSame(3, $childrenChild->level());
    }

    public function testAssignParentFamilyLogFailWithFamilyLogAlreadyExists(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $repository = $this->createMock(FamilyLogRepository::class);
        $useCase = new AssignParentFamilyLog($repository);
        $familyLogBuilder = new FamilyLogDataBuilder();
        $parent = $familyLogBuilder->create('Surgelé')
            ->withUuid($faker->uuid())
            ->build()
        ;

        $familyLog = $familyLogBuilder->create('Viande')->build();

        $request = $this->createMock(AssignParentFamilyLogRequest::class);
        $request->expects(self::once())->method('uuid')->willReturn(FamilyLogDataBuilder::VALID_UUID);
        $request->expects(self::once())->method('parent')->willReturn($parent);

        $repository->expects(self::once())
            ->method('findByUuid')
            ->with(ResourceUuid::fromString(FamilyLogDataBuilder::VALID_UUID))
            ->willReturn($familyLog)
        ;

        $repository->expects(once())
            ->method('exists')
            ->with('Viande', $parent)
            ->willReturn(true)
        ;

        $repository->expects(self::never())
            ->method('assignParent')
            ->with($familyLog)
        ;

        // Act && Assert
        $this->expectException(FamilyLogAlreadyExistsException::class);

        $useCase->execute($request);
    }

    public function testAssignParentFamilyLogFailWithNotFoundException(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $repository = $this->createMock(FamilyLogRepository::class);
        $useCase = new AssignParentFamilyLog($repository);
        $familyLogBuilder = new FamilyLogDataBuilder();
        $parent = $familyLogBuilder->create('Surgelé')
            ->withUuid($faker->uuid())
            ->build()
        ;

        $request = $this->createMock(AssignParentFamilyLogRequest::class);
        $request->expects(self::once())->method('uuid')->willReturn(FamilyLogDataBuilder::VALID_UUID);
        $request->expects(self::never())->method('parent')->willReturn($parent);

        $repository->expects(self::once())
            ->method('findByUuid')
            ->with(ResourceUuid::fromString(FamilyLogDataBuilder::VALID_UUID))
            ->will(self::throwException(new FamilyLogNotFoundException(FamilyLogDataBuilder::VALID_UUID)))
        ;

        $repository->expects(self::never())
            ->method('exists')
        ;

        $repository->expects(self::never())
            ->method('assignParent')
        ;

        // Act && Assert
        $this->expectException(FamilyLogNotFoundException::class);

        $useCase->execute($request);
    }
}
