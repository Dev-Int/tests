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

namespace Shared\Tests\Adapters\Gateway\Filter;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shared\Adapters\Gateway\Filter\JsonContainsFilter;
use Shared\Entities\ResourceUuid;

/**
 * @group unitTest
 *
 * @covers \Shared\Adapters\Gateway\Filter\JsonContainsFilter
 */
final class JsonContainsFilterTest extends TestCase
{
    private MockObject&QueryBuilder $queryBuilder;
    private JsonContainsFilter $filter;

    protected function setUp(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $this->queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->setConstructorArgs([$entityManager])
            ->onlyMethods(['andWhere', 'setParameter'])
            ->getMock()
        ;
        $this->filter = new JsonContainsFilter();
    }

    public function testIsApplicableReturnsTrueForResourceUuid(): void
    {
        // Arrange
        $uuid = ResourceUuid::fromString('550e8400-e29b-41d4-a716-446655440000');

        // Act
        $result = $this->filter->isApplicable($uuid);

        // Assert
        self::assertTrue($result);
    }

    public function testIsApplicableReturnsTrueForNonEmptyString(): void
    {
        // Arrange & Act
        $result = $this->filter->isApplicable('zone-storage-id');

        // Assert
        self::assertTrue($result);
    }

    public function testIsApplicableReturnsFalseForNull(): void
    {
        // Arrange & Act
        $result = $this->filter->isApplicable(null);

        // Assert
        self::assertFalse($result);
    }

    public function testIsApplicableReturnsFalseForEmptyString(): void
    {
        // Arrange & Act
        $result = $this->filter->isApplicable('');

        // Assert
        self::assertFalse($result);
    }

    public function testApplyWithResourceUuid(): void
    {
        // Arrange
        $uuid = ResourceUuid::fromString('550e8400-e29b-41d4-a716-446655440000');

        $this->queryBuilder->expects(self::once())
            ->method('andWhere')
            ->with('TEXT(entity.zoneStorages) LIKE :param_zone')
            ->willReturnSelf()
        ;
        $this->queryBuilder->expects(self::once())
            ->method('setParameter')
            ->with('param_zone', '%"550e8400-e29b-41d4-a716-446655440000"%')
            ->willReturnSelf()
        ;

        // Act
        $this->filter->apply($this->queryBuilder, 'entity', 'zoneStorages', $uuid, 'param_zone');
    }

    public function testApplyWithStringValue(): void
    {
        // Arrange
        $this->queryBuilder->expects(self::once())
            ->method('andWhere')
            ->with('TEXT(entity.tags) LIKE :param_tags')
            ->willReturnSelf()
        ;
        $this->queryBuilder->expects(self::once())
            ->method('setParameter')
            ->with('param_tags', '%"important"%')
            ->willReturnSelf()
        ;

        // Act
        $this->filter->apply($this->queryBuilder, 'entity', 'tags', 'important', 'param_tags');
    }

    public function testApplyThrowsExceptionForInvalidType(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('JsonContainsFilter value must be string or ResourceUuid');

        // Act
        $this->filter->apply($this->queryBuilder, 'entity', 'data', 123, 'param');
    }

    public function testApplyEscapesPercentCharacter(): void
    {
        // Arrange - Value containing LIKE wildcard %
        $this->queryBuilder->expects(self::once())
            ->method('andWhere')
            ->with('TEXT(entity.tags) LIKE :param_tags')
            ->willReturnSelf()
        ;
        $this->queryBuilder->expects(self::once())
            ->method('setParameter')
            ->with('param_tags', '%"100\%"%')
            ->willReturnSelf()
        ;

        // Act
        $this->filter->apply($this->queryBuilder, 'entity', 'tags', '100%', 'param_tags');
    }

    public function testApplyEscapesUnderscoreCharacter(): void
    {
        // Arrange - Value containing LIKE wildcard _
        $this->queryBuilder->expects(self::once())
            ->method('andWhere')
            ->with('TEXT(entity.tags) LIKE :param_tags')
            ->willReturnSelf()
        ;
        $this->queryBuilder->expects(self::once())
            ->method('setParameter')
            ->with('param_tags', '%"zone\_name"%')
            ->willReturnSelf()
        ;

        // Act
        $this->filter->apply($this->queryBuilder, 'entity', 'tags', 'zone_name', 'param_tags');
    }

    public function testApplyEscapesBackslashCharacter(): void
    {
        // Arrange - Value containing backslash
        $this->queryBuilder->expects(self::once())
            ->method('andWhere')
            ->with('TEXT(entity.tags) LIKE :param_tags')
            ->willReturnSelf()
        ;
        $this->queryBuilder->expects(self::once())
            ->method('setParameter')
            ->with('param_tags', '%"path\\\file"%')
            ->willReturnSelf()
        ;

        // Act
        $this->filter->apply($this->queryBuilder, 'entity', 'tags', 'path\file', 'param_tags');
    }
}
