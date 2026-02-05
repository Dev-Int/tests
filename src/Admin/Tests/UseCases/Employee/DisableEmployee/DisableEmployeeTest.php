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

namespace Admin\Tests\UseCases\Employee\DisableEmployee;

use Admin\Entities\Exception\Employee\EmployeeAlreadyDisabled;
use Admin\Entities\Exception\Employee\EmployeeNotFound;
use Admin\Entities\Repository\EmployeeRepository;
use Admin\Tests\DataBuilder\EmployeeDataBuilder;
use Admin\UseCases\Employee\DisableEmployee\DisableEmployee;
use Admin\UseCases\Employee\DisableEmployee\DisableEmployeeRequest;
use Admin\UseCases\Employee\Exception\UserAlreadyDisabled;
use Admin\UseCases\Employee\Exception\UserNotFound;
use Admin\UseCases\Gateway\UserDisablerGateway;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shared\Entities\ResourceUuid;

/**
 * @group unitTest
 *
 * @covers \Admin\UseCases\Employee\DisableEmployee\DisableEmployee
 */
final class DisableEmployeeTest extends TestCase
{
    private DisableEmployee $useCase;
    private EmployeeRepository&MockObject $repository;
    private MockObject&UserDisablerGateway $userDisabler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(EmployeeRepository::class);
        $this->userDisabler = $this->createMock(UserDisablerGateway::class);
        $this->useCase = new DisableEmployee($this->repository, $this->userDisabler);
    }

    public function testDisableEmployeeWithSuccess(): void
    {
        // Arrange
        $uuid = ResourceUuid::generate();
        $userUuid = ResourceUuid::generate();
        $employee = EmployeeDataBuilder::anEmployee()
            ->withUuid($uuid)
            ->withUserUuid($userUuid)
            ->build()
        ;

        $request = $this->createMock(DisableEmployeeRequest::class);
        $request->expects(self::once())
            ->method('uuid')
            ->willReturn($uuid)
        ;

        $this->repository->expects(self::once())
            ->method('getByUuid')
            ->with($uuid)
            ->willReturn($employee)
        ;

        $this->userDisabler->expects(self::once())
            ->method('disableUser')
            ->with($userUuid)
        ;

        $this->repository->expects(self::once())
            ->method('update')
            ->with($employee)
        ;

        // Act
        $response = $this->useCase->execute($request);

        // Assert
        self::assertNotNull($response->employee()->disabledAt());
    }

    public function testDisableEmployeeThrowsExceptionWhenNotFound(): void
    {
        // Arrange
        $uuid = ResourceUuid::generate();

        $request = $this->createMock(DisableEmployeeRequest::class);
        $request->expects(self::once())
            ->method('uuid')
            ->willReturn($uuid)
        ;

        $this->repository->expects(self::once())
            ->method('getByUuid')
            ->with($uuid)
            ->willThrowException(new EmployeeNotFound($uuid))
        ;

        $this->repository->expects(self::never())
            ->method('update')
        ;

        // Assert
        $this->expectException(EmployeeNotFound::class);

        // Act
        $this->useCase->execute($request);
    }

    public function testDisableEmployeeThrowsExceptionWhenAlreadyDisabled(): void
    {
        // Arrange
        $uuid = ResourceUuid::generate();
        $employee = EmployeeDataBuilder::anEmployee()
            ->withUuid($uuid)
            ->disabled() // Employee déjà désactivé
            ->build()
        ;

        $request = $this->createMock(DisableEmployeeRequest::class);
        $request->expects(self::once())
            ->method('uuid')
            ->willReturn($uuid)
        ;

        $this->repository->expects(self::once())
            ->method('getByUuid')
            ->with($uuid)
            ->willReturn($employee)
        ;

        $this->repository->expects(self::never())
            ->method('update')
        ;

        // Assert
        $this->expectException(EmployeeAlreadyDisabled::class);

        // Act
        $this->useCase->execute($request);
    }

    public function testDisableEmployeeThrowsExceptionWhenUserAlreadyDisabled(): void
    {
        // Arrange
        $uuid = ResourceUuid::generate();
        $userUuid = ResourceUuid::generate();
        $employee = EmployeeDataBuilder::anEmployee()
            ->withUuid($uuid)
            ->withUserUuid($userUuid)
            ->build()
        ;

        $request = $this->createMock(DisableEmployeeRequest::class);
        $request->expects(self::once())
            ->method('uuid')
            ->willReturn($uuid)
        ;

        $this->repository->expects(self::once())
            ->method('getByUuid')
            ->with($uuid)
            ->willReturn($employee)
        ;

        $this->userDisabler->expects(self::once())
            ->method('disableUser')
            ->with($userUuid)
            ->willThrowException(new UserAlreadyDisabled($userUuid))
        ;

        $this->repository->expects(self::never())
            ->method('update')
        ;

        // Assert
        $this->expectException(UserAlreadyDisabled::class);

        // Act
        $this->useCase->execute($request);
    }

    public function testDisableEmployeeThrowsExceptionWhenUserNotFound(): void
    {
        // Arrange
        $uuid = ResourceUuid::generate();
        $userUuid = ResourceUuid::generate();
        $employee = EmployeeDataBuilder::anEmployee()
            ->withUuid($uuid)
            ->withUserUuid($userUuid)
            ->build()
        ;

        $request = $this->createMock(DisableEmployeeRequest::class);
        $request->expects(self::once())
            ->method('uuid')
            ->willReturn($uuid)
        ;

        $this->repository->expects(self::once())
            ->method('getByUuid')
            ->with($uuid)
            ->willReturn($employee)
        ;

        $this->userDisabler->expects(self::once())
            ->method('disableUser')
            ->with($userUuid)
            ->willThrowException(new UserNotFound($userUuid))
        ;

        $this->repository->expects(self::never())
            ->method('update')
        ;

        // Assert
        $this->expectException(UserNotFound::class);

        // Act
        $this->useCase->execute($request);
    }
}
