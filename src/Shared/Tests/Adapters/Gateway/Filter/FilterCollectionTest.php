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
use Shared\Adapters\Gateway\Filter\FilterCollection;
use Shared\Adapters\Gateway\Filter\SearchFilter;

/**
 * @group unitTest
 *
 * @covers \Shared\Adapters\Gateway\Filter\FilterCollection
 */
final class FilterCollectionTest extends TestCase
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

    public function testAddReturnsSelfForFluentChaining(): void
    {
        // Arrange
        $collection = new FilterCollection();

        // Act
        $result = $collection->add(new SearchFilter(), 'entity', 'status', 'active');

        // Assert
        self::assertSame($collection, $result);
    }

    public function testAddIgnoresNonApplicableFilters(): void
    {
        // Arrange
        $collection = new FilterCollection();

        // No calls expected since null is not applicable
        $this->queryBuilder->expects(self::never())
            ->method('andWhere')
        ;

        // Act
        $collection
            ->add(new SearchFilter(), 'entity', 'status', null)
            ->apply($this->queryBuilder)
        ;
    }

    public function testApplyCallsAllApplicableFilters(): void
    {
        // Arrange
        $collection = new FilterCollection();
        $date = new \DateTimeImmutable('2024-06-15');

        $this->queryBuilder->expects(self::exactly(2))
            ->method('andWhere')
            ->willReturnSelf()
        ;
        $this->queryBuilder->expects(self::exactly(2))
            ->method('setParameter')
            ->willReturnSelf()
        ;

        // Act
        $collection
            ->add(new SearchFilter(), 'entity', 'status', 'active')
            ->add(DateFilter::after(), 'entity', 'createdAt', $date)
            ->apply($this->queryBuilder)
        ;
    }

    public function testApplyGeneratesUniqueParameterNames(): void
    {
        // Arrange
        $collection = new FilterCollection();
        $afterDate = new \DateTimeImmutable('2024-01-01');
        $beforeDate = new \DateTimeImmutable('2024-12-31');
        $capturedParams = [];

        $this->queryBuilder->expects(self::exactly(2))
            ->method('andWhere')
            ->willReturnCallback(function (string $where) use (&$capturedParams): MockObject&QueryBuilder {
                // Extract parameter name from WHERE clause
                if (preg_match('/:(\w+)/', $where, $matches) === 1) {
                    $capturedParams[] = $matches[1];
                }

                return $this->queryBuilder;
            })
        ;
        $this->queryBuilder->expects(self::exactly(2))
            ->method('setParameter')
            ->willReturnSelf()
        ;

        // Act
        $collection
            ->add(DateFilter::after(), 'entity', 'date', $afterDate)
            ->add(DateFilter::before(), 'entity', 'date', $beforeDate)
            ->apply($this->queryBuilder)
        ;

        // Assert - parameters should be unique
        self::assertCount(2, $capturedParams);
        self::assertSame('filter_date_1', $capturedParams[0]);
        self::assertSame('filter_date_2', $capturedParams[1]);
    }

    public function testMixedApplicabilityOnlyAppliesValidFilters(): void
    {
        // Arrange
        $collection = new FilterCollection();

        // Only one call expected (status), date is null so not applicable
        $this->queryBuilder->expects(self::once())
            ->method('andWhere')
            ->with('entity.status = :filter_status_1')
            ->willReturnSelf()
        ;
        $this->queryBuilder->expects(self::once())
            ->method('setParameter')
            ->with('filter_status_1', 'active')
            ->willReturnSelf()
        ;

        // Act
        $collection
            ->add(new SearchFilter(), 'entity', 'status', 'active')
            ->add(DateFilter::after(), 'entity', 'createdAt', null)
            ->apply($this->queryBuilder)
        ;
    }
}
