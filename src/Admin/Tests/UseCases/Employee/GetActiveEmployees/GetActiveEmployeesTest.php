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

namespace Admin\Tests\UseCases\Employee\GetActiveEmployees;

use Admin\Entities\Employee\EmployeeCollection;
use Admin\Entities\Exception\Employee\NoEmployeeRegistered;
use Admin\Entities\Repository\EmployeeRepository;
use Admin\Tests\DataBuilder\EmployeeDataBuilder;
use Admin\UseCases\Employee\GetActiveEmployees\GetActiveEmployees;
use Admin\UseCases\Employee\GetActiveEmployees\GetActiveEmployeesApiRequest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 *
 * @covers \Admin\UseCases\Employee\GetActiveEmployees\GetActiveEmployees
 */
final class GetActiveEmployeesTest extends TestCase
{
    private EmployeeRepository&MockObject $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(EmployeeRepository::class);
    }

    public function testGetActiveEmployeesWithSuccess(): void
    {
        // Arrange
        $employee1 = EmployeeDataBuilder::anEmployee()
            ->withEmail('john.doe@example.com')
            ->build()
        ;
        $employee2 = EmployeeDataBuilder::anEmployee()
            ->withEmail('jane.smith@example.com')
            ->build()
        ;

        $collection = new EmployeeCollection(2);
        $collection->add($employee1);
        $collection->add($employee2);

        $this->repository
            ->expects(self::once())
            ->method('getActiveEmployeesPaginated')
            ->with(1, 20)
            ->willReturn($collection)
        ;

        $useCase = new GetActiveEmployees($this->repository);

        // Act
        $response = $useCase->execute(new GetActiveEmployeesApiRequest(1, 20));

        // Assert
        self::assertCount(2, $response->employees());
        self::assertSame($employee1, $response->employees()[0]);
        self::assertSame($employee2, $response->employees()[1]);
    }

    public function testGetActiveEmployeesWillFailWhenNoEmployeeRegistered(): void
    {
        // Arrange
        $this->repository
            ->expects(self::once())
            ->method('getActiveEmployeesPaginated')
            ->with(1, 20)
            ->willThrowException(new NoEmployeeRegistered())
        ;

        $useCase = new GetActiveEmployees($this->repository);

        // Assert
        $this->expectException(NoEmployeeRegistered::class);

        // Act
        $useCase->execute(new GetActiveEmployeesApiRequest(1, 20));
    }

    public function testGetActiveEmployeesPaginatedReturnsFirstPage(): void
    {
        // Arrange
        $employee1 = EmployeeDataBuilder::anEmployee()
            ->withEmail('first@example.com')
            ->build()
        ;
        $employee2 = EmployeeDataBuilder::anEmployee()
            ->withEmail('second@example.com')
            ->build()
        ;

        $collection = new EmployeeCollection(2);
        $collection->add($employee1);
        $collection->add($employee2);

        $this->repository
            ->expects(self::once())
            ->method('getActiveEmployeesPaginated')
            ->with(1, 20)
            ->willReturn($collection)
        ;

        $useCase = new GetActiveEmployees($this->repository);

        // Act
        $response = $useCase->execute(new GetActiveEmployeesApiRequest(1, 20));

        // Assert
        self::assertCount(2, $response->employees());
    }

    public function testGetActiveEmployeesPaginatedReturnsSecondPage(): void
    {
        // Arrange
        $employee1 = EmployeeDataBuilder::anEmployee()
            ->withEmail('page2first@example.com')
            ->build()
        ;

        $collection = new EmployeeCollection(1);
        $collection->add($employee1);

        $this->repository
            ->expects(self::once())
            ->method('getActiveEmployeesPaginated')
            ->with(2, 20)
            ->willReturn($collection)
        ;

        $useCase = new GetActiveEmployees($this->repository);

        // Act
        $response = $useCase->execute(new GetActiveEmployeesApiRequest(2, 20));

        // Assert
        self::assertCount(1, $response->employees());
    }

    public function testGetActiveEmployeesPaginatedRespectsItemsPerPage(): void
    {
        // Arrange
        $employees = [];
        $collection = new EmployeeCollection(10);

        for ($i = 1; $i <= 10; ++$i) {
            $employee = EmployeeDataBuilder::anEmployee()
                ->withEmail("employee{$i}@example.com")
                ->build()
            ;
            $employees[] = $employee;
            $collection->add($employee);
        }

        $this->repository
            ->expects(self::once())
            ->method('getActiveEmployeesPaginated')
            ->with(1, 10)
            ->willReturn($collection)
        ;

        $useCase = new GetActiveEmployees($this->repository);

        // Act
        $response = $useCase->execute(new GetActiveEmployeesApiRequest(1, 10));

        // Assert
        self::assertCount(10, $response->employees());
    }
}
