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
use Admin\Tests\DataBuilder\EmployeeDataBuilder;
use Admin\UseCases\Employee\GetActiveEmployees\GetActiveEmployees;
use Admin\UseCases\Employee\GetActiveEmployees\GetActiveEmployeesRequest;
use Admin\UseCases\Gateway\Finder\EmployeeFinder;
use Faker\Factory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 *
 * @covers \Admin\UseCases\Employee\GetActiveEmployees\GetActiveEmployees
 */
final class GetActiveEmployeesTest extends TestCase
{
    private EmployeeFinder&MockObject $employeeFinder;

    protected function setUp(): void
    {
        $this->employeeFinder = $this->createMock(EmployeeFinder::class);
    }

    public function testGetActiveEmployeesWithSuccess(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $collection = new EmployeeCollection(45);
        $employees = [];
        foreach (range(1, 45) as $iter) {
            $employee = EmployeeDataBuilder::anEmployee()
                ->withEmail($faker->email())
                ->build()
            ;
            if ($iter <= 20) {
                $collection->add($employee);
            }
            $employees[$iter] = $employee;
        }
        $request = $this->createMock(GetActiveEmployeesRequest::class);

        // Assert
        $request->expects(self::once())->method('page')->willReturn(1);
        $request->expects(self::once())->method('itemsPerPage')->willReturn(20);

        $this->employeeFinder->expects(self::once())
            ->method('getActiveEmployeesPaginated')
            ->with(1, 20)
            ->willReturn($collection)
        ;
        $this->employeeFinder->expects(self::once())
            ->method('getActiveEmployeesCount')
            ->willReturn(45)
        ;

        $useCase = new GetActiveEmployees($this->employeeFinder);

        // Act
        $response = $useCase->execute($request);

        // Assert
        self::assertSame(45, $response->totalCount());
        self::assertCount(20, $response->employees());
        self::assertSame($employees[1], $response->employees()[0]);
        self::assertSame($employees[2], $response->employees()[1]);
    }

    public function testGetActiveEmployeesWillFailWhenNoEmployeeRegistered(): void
    {
        // Arrange
        $this->employeeFinder
            ->expects(self::once())
            ->method('getActiveEmployeesPaginated')
            ->with(1, 20)
            ->willThrowException(new NoEmployeeRegistered())
        ;

        $useCase = new GetActiveEmployees($this->employeeFinder);
        $request = $this->createMock(GetActiveEmployeesRequest::class);

        // Assert
        $request->expects(self::once())->method('page')->willReturn(1);
        $request->expects(self::once())->method('itemsPerPage')->willReturn(20);

        $this->employeeFinder->expects(self::once())
            ->method('getActiveEmployeesPaginated')
            ->with(1, 20)
            ->willReturn(new EmployeeCollection(0))
        ;

        $this->expectException(NoEmployeeRegistered::class);

        // Act
        $useCase->execute($request);
    }
}
