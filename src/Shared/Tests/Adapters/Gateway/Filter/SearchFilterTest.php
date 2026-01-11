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
use Shared\Adapters\Gateway\Filter\SearchFilter;

/**
 * @group unitTest
 *
 * @covers \Shared\Adapters\Gateway\Filter\SearchFilter
 */
final class SearchFilterTest extends TestCase
{
    private MockObject&QueryBuilder $queryBuilder;
    private SearchFilter $filter;

    protected function setUp(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $this->queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->setConstructorArgs([$entityManager])
            ->onlyMethods(['andWhere', 'setParameter'])
            ->getMock()
        ;
        $this->filter = new SearchFilter();
    }

    public function testIsApplicableReturnsFalseForNull(): void
    {
        // Arrange & Act
        $result = $this->filter->isApplicable(null);

        // Assert
        self::assertFalse($result);
    }

    public function testIsApplicableReturnsTrueForString(): void
    {
        // Arrange & Act
        $result = $this->filter->isApplicable('active');

        // Assert
        self::assertTrue($result);
    }

    public function testIsApplicableReturnsTrueForBackedEnum(): void
    {
        // Arrange & Act
        $result = $this->filter->isApplicable(TestStatus::ACTIVE);

        // Assert
        self::assertTrue($result);
    }

    public function testApplyWithStringValue(): void
    {
        // Arrange
        $this->queryBuilder->expects(self::once())
            ->method('andWhere')
            ->with('entity.status = :param_status')
            ->willReturnSelf()
        ;
        $this->queryBuilder->expects(self::once())
            ->method('setParameter')
            ->with('param_status', 'active')
            ->willReturnSelf()
        ;

        // Act
        $this->filter->apply($this->queryBuilder, 'entity', 'status', 'active', 'param_status');
    }

    public function testApplyWithBackedEnumExtractsValue(): void
    {
        // Arrange
        $this->queryBuilder->expects(self::once())
            ->method('andWhere')
            ->with('entity.status = :param_status')
            ->willReturnSelf()
        ;
        $this->queryBuilder->expects(self::once())
            ->method('setParameter')
            ->with('param_status', 'active')
            ->willReturnSelf()
        ;

        // Act
        $this->filter->apply($this->queryBuilder, 'entity', 'status', TestStatus::ACTIVE, 'param_status');
    }
}
