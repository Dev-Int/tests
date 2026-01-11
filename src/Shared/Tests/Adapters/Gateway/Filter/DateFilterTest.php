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
use Shared\Adapters\Gateway\Filter\DateFilter;

/**
 * @group unitTest
 *
 * @covers \Shared\Adapters\Gateway\Filter\DateFilter
 */
final class DateFilterTest extends TestCase
{
    private MockObject&QueryBuilder $queryBuilder;

    protected function setUp(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $this->queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->setConstructorArgs([$entityManager])
            ->onlyMethods(['andWhere', 'setParameter'])
            ->getMock()
        ;
    }

    public function testIsApplicableReturnsTrueForDateTimeImmutable(): void
    {
        // Arrange
        $filter = DateFilter::after();

        // Act
        $result = $filter->isApplicable(new \DateTimeImmutable('2024-01-15'));

        // Assert
        self::assertTrue($result);
    }

    public function testIsApplicableReturnsTrueForDateTime(): void
    {
        // Arrange
        $filter = DateFilter::before();

        // Act
        $result = $filter->isApplicable(new \DateTime('2024-01-15'));

        // Assert
        self::assertTrue($result);
    }

    public function testIsApplicableReturnsFalseForNull(): void
    {
        // Arrange
        $filter = DateFilter::after();

        // Act
        $result = $filter->isApplicable(null);

        // Assert
        self::assertFalse($result);
    }

    public function testIsApplicableReturnsFalseForString(): void
    {
        // Arrange
        $filter = DateFilter::after();

        // Act
        $result = $filter->isApplicable('2024-01-15');

        // Assert
        self::assertFalse($result);
    }

    public function testAfterAppliesGreaterOrEqualOperator(): void
    {
        // Arrange
        $filter = DateFilter::after();
        $date = new \DateTimeImmutable('2024-01-15');

        $this->queryBuilder->expects(self::once())
            ->method('andWhere')
            ->with('entity.createdAt >= :param_date')
            ->willReturnSelf()
        ;
        $this->queryBuilder->expects(self::once())
            ->method('setParameter')
            ->with('param_date', $date)
            ->willReturnSelf()
        ;

        // Act
        $filter->apply($this->queryBuilder, 'entity', 'createdAt', $date, 'param_date');
    }

    public function testBeforeAppliesLessOrEqualOperator(): void
    {
        // Arrange
        $filter = DateFilter::before();
        $date = new \DateTimeImmutable('2024-12-31');

        $this->queryBuilder->expects(self::once())
            ->method('andWhere')
            ->with('entity.createdAt <= :param_date')
            ->willReturnSelf()
        ;
        $this->queryBuilder->expects(self::once())
            ->method('setParameter')
            ->with('param_date', $date)
            ->willReturnSelf()
        ;

        // Act
        $filter->apply($this->queryBuilder, 'entity', 'createdAt', $date, 'param_date');
    }
}
