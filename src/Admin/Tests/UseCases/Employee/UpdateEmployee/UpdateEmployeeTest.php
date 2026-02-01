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

use Admin\Entities\Exception\Employee\EmployeeNotFound;
use Admin\Entities\Repository\EmployeeRepository;
use Admin\Tests\DataBuilder\EmployeeDataBuilder;
use Admin\UseCases\Employee\UpdateEmployee\UpdateEmployee;
use Admin\UseCases\Employee\UpdateEmployee\UpdateEmployeeRequest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shared\Entities\ResourceUuid;
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

    public function testUpdatePhoneWithSuccess(): void
    {
        // Arrange
        $request = $this->createMock(UpdateEmployeeRequest::class);
        $useCase = new UpdateEmployee($this->repository);

        $uuid = ResourceUuid::generate();
        $employee = EmployeeDataBuilder::anEmployee()
            ->withUuid($uuid)
            ->build()
        ;

        $newPhone = PhoneField::fromString('0698765432');

        // Assert
        $request->expects(self::once())->method('uuid')->willReturn($uuid);
        $request->expects(self::once())
            ->method('phone')
            ->willReturn($newPhone)
        ;
        $request->expects(self::once())->method('position')->willReturn($employee->position());
        $request->expects(self::once())->method('department')->willReturn($employee->department());

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
        self::assertSame($uuid->toString(), $response->employee()->uuid()->toString(), 'UUID doit être inchangé');
        self::assertSame(
            '0698765432',
            $response->employee()->contactInformation()->phone()->toNumber(),
            'Téléphone doit être mis à jour'
        );
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
        $request->expects(self::once())
            ->method('phone')
            ->willReturn($employee->contactInformation()->phone())
        ;
        $request->expects(self::once())->method('position')->willReturn($newPosition);
        $request->expects(self::once())->method('department')->willReturn($newDepartment);

        // Act
        $response = $useCase->execute($request);

        // Assert
        self::assertSame(
            'Senior Developer',
            $response->employee()->position()->toString(),
            'Position doit être mise à jour'
        );
        self::assertSame(
            'Engineering',
            $response->employee()->department()->toString(),
            'Département doit être mis à jour'
        );
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
        $request->expects(self::never())->method('phone');
        $request->expects(self::never())->method('position');
        $request->expects(self::never())->method('department');

        // Assert
        $this->expectException(EmployeeNotFound::class);

        // Act
        $useCase->execute($request);
    }
}
