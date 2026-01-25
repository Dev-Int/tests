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

namespace Admin\Tests\UseCases\Employee\GetEmployees;

use Admin\Entities\Employee\EmployeeCollection;
use Admin\Entities\Exception\Employee\NoEmployeeRegistered;
use Admin\Entities\Repository\EmployeeRepository;
use Admin\Tests\DataBuilder\EmployeeDataBuilder;
use Admin\UseCases\Employee\GetEmployees\GetEmployees;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 *
 * @covers \Admin\UseCases\Employee\GetEmployees\GetEmployees
 */
final class GetEmployeesTest extends TestCase
{
    private EmployeeRepository&MockObject $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(EmployeeRepository::class);
    }

    public function testGetEmployeesWithSuccess(): void
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
            ->method('getAllEmployees')
            ->willReturn($collection)
        ;

        $useCase = new GetEmployees($this->repository);

        // Act
        $response = $useCase->execute();

        // Assert
        self::assertCount(2, $response->employees());
        self::assertSame($employee1, $response->employees()[0]);
        self::assertSame($employee2, $response->employees()[1]);
    }

    public function testGetEmployeesWillFailWhenNoEmployeeRegistered(): void
    {
        // Arrange
        $this->repository
            ->expects(self::once())
            ->method('getAllEmployees')
            ->willThrowException(new NoEmployeeRegistered())
        ;

        $useCase = new GetEmployees($this->repository);

        // Assert
        $this->expectException(NoEmployeeRegistered::class);

        // Act
        $useCase->execute();
    }
}
