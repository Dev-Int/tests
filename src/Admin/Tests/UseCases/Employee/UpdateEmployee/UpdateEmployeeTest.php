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

namespace Admin\Tests\UseCases\Employee\UpdateEmployee;

use Admin\Entities\Employee\ContactInformation;
use Admin\Entities\Exception\Employee\EmployeeNotFound;
use Admin\Entities\Repository\EmployeeRepository;
use Admin\Entities\VO\EmployeeStatus;
use Admin\Tests\DataBuilder\EmployeeDataBuilder;
use Admin\UseCases\Employee\UpdateEmployee\UpdateEmployee;
use Admin\UseCases\Employee\UpdateEmployee\UpdateEmployeeRequest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\EmailField;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\PhoneField;

/**
 * @group unitTest
 *
 * @covers \Admin\UseCases\Employee\UpdateEmployee\UpdateEmployee
 */
final class UpdateEmployeeTest extends TestCase
{
    private EmployeeRepository&MockObject $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(EmployeeRepository::class);
    }

    public function testUpdateContactInfoWithSuccess(): void
    {
        // Arrange
        $request = $this->createMock(UpdateEmployeeRequest::class);
        $useCase = new UpdateEmployee($this->repository);

        $uuid = ResourceUuid::generate();
        $employee = EmployeeDataBuilder::anEmployee()
            ->withUuid($uuid)
            ->withEmail('old.email@example.com')
            ->build()
        ;

        $newEmail = EmailField::fromString('new.email@example.com');
        $newPhone = PhoneField::fromString('0698765432');

        // Assert
        $request->expects(self::once())->method('uuid')->willReturn($uuid);
        $request->expects(self::once())
            ->method('contactInformation')
            ->willReturn(new ContactInformation($newEmail, $newPhone))
        ;
        $request->expects(self::once())->method('position')->willReturn($employee->position());
        $request->expects(self::once())->method('department')->willReturn($employee->department());
        $request->expects(self::once())->method('status')->willReturn($employee->status());

        $this->repository
            ->expects(self::once())
            ->method('getByUuid')
            ->with($uuid)
            ->willReturn($employee)
        ;

        $this->repository
            ->expects(self::once())
            ->method('update')
        ;

        // Act
        $response = $useCase->execute($request);

        // Assert
        self::assertSame($uuid->toString(), $response->employee()->uuid()->toString());
        self::assertSame('new.email@example.com', $response->employee()->contactInformation()->email->toString());
        self::assertSame('0698765432', $response->employee()->contactInformation()->phone->toNumber());
    }

    public function testUpdatePositionWithSuccess(): void
    {
        // Arrange
        $request = $this->createMock(UpdateEmployeeRequest::class);
        $useCase = new UpdateEmployee($this->repository);

        $uuid = ResourceUuid::generate();
        $employee = EmployeeDataBuilder::anEmployee()
            ->withUuid($uuid)
            ->withPosition('Developer')
            ->withDepartment('IT')
            ->build()
        ;

        $newPosition = NameField::fromString('Senior Developer');
        $newDepartment = NameField::fromString('Engineering');

        // Assert
        $this->repository
            ->expects(self::once())
            ->method('getByUuid')
            ->with($uuid)
            ->willReturn($employee)
        ;

        $this->repository
            ->expects(self::once())
            ->method('update')
        ;

        $request->expects(self::once())->method('uuid')->willReturn($uuid);
        $request->expects(self::once())->method('contactInformation')->willReturn($employee->contactInformation());
        $request->expects(self::once())->method('position')->willReturn($newPosition);
        $request->expects(self::once())->method('department')->willReturn($newDepartment);
        $request->expects(self::once())->method('status')->willReturn($employee->status());

        // Act
        $response = $useCase->execute($request);

        // Assert
        self::assertSame('Senior Developer', $response->employee()->position()->toString());
        self::assertSame('Engineering', $response->employee()->department()->toString());
    }

    public function testChangeStatusWithSuccess(): void
    {
        // Arrange
        $request = $this->createMock(UpdateEmployeeRequest::class);
        $useCase = new UpdateEmployee($this->repository);

        $uuid = ResourceUuid::generate();
        $employee = EmployeeDataBuilder::anEmployee()
            ->withUuid($uuid)
            ->withStatus(EmployeeStatus::ACTIVE)
            ->build()
        ;

        $this->repository
            ->expects(self::once())
            ->method('getByUuid')
            ->with($uuid)
            ->willReturn($employee)
        ;

        $this->repository
            ->expects(self::once())
            ->method('update')
        ;

        $request->expects(self::once())->method('uuid')->willReturn($uuid);
        $request->expects(self::once())->method('contactInformation')->willReturn($employee->contactInformation());
        $request->expects(self::once())->method('position')->willReturn($employee->position());
        $request->expects(self::once())->method('department')->willReturn($employee->department());
        $request->expects(self::once())->method('status')->willReturn(EmployeeStatus::INACTIVE);

        // Act
        $response = $useCase->execute($request);

        // Assert
        self::assertSame(EmployeeStatus::INACTIVE, $response->employee()->status());
    }

    public function testUpdateEmployeeWillFailWhenNotFound(): void
    {
        // Arrange
        $request = $this->createMock(UpdateEmployeeRequest::class);
        $useCase = new UpdateEmployee($this->repository);

        $uuid = ResourceUuid::generate();

        // Assert
        $this->repository
            ->expects(self::once())
            ->method('getByUuid')
            ->with($uuid)
            ->willThrowException(new EmployeeNotFound($uuid))
        ;

        $this->repository
            ->expects(self::never())
            ->method('update')
        ;

        $request->expects(self::once())->method('uuid')->willReturn($uuid);
        $request->expects(self::never())->method('contactInformation');
        $request->expects(self::never())->method('position');
        $request->expects(self::never())->method('department');
        $request->expects(self::never())->method('status');

        // Assert
        $this->expectException(EmployeeNotFound::class);

        // Act
        $useCase->execute($request);
    }
}
