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

namespace Admin\Tests\UseCases\Employee\CreateEmployee;

use Admin\Entities\Employee\Employee;
use Admin\Entities\Exception\Employee\EmployeeAlreadyExists;
use Admin\Entities\Repository\EmployeeRepository;
use Admin\UseCases\DTO\CreatedUserDTO;
use Admin\UseCases\DTO\CreateUserDTO;
use Admin\UseCases\Employee\CreateEmployee\CreateEmployee;
use Admin\UseCases\Employee\CreateEmployee\CreateEmployeeRequest;
use Admin\UseCases\Employee\Exception\UserEmailAlreadyExists;
use Admin\UseCases\Gateway\NotificationGateway;
use Admin\UseCases\Gateway\PasswordResetGateway;
use Admin\UseCases\Gateway\UserCreatorGateway;
use PHPUnit\Framework\TestCase;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\EmailField;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\PhoneField;

/**
 * @group unitTest
 */
final class CreateEmployeeTest extends TestCase
{
    public function testCreateEmployeeWithSuccess(): void
    {
        // Arrange
        $employeeRepository = $this->createMock(EmployeeRepository::class);
        $userCreatorGateway = $this->createMock(UserCreatorGateway::class);
        $passwordResetTokenCreator = $this->createMock(PasswordResetGateway::class);
        $notificationGateway = $this->createMock(NotificationGateway::class);
        $useCase = new CreateEmployee(
            $employeeRepository,
            $userCreatorGateway,
            $passwordResetTokenCreator,
            $notificationGateway
        );
        $request = $this->createMock(CreateEmployeeRequest::class);

        $email = EmailField::fromString('john.doe@example.com');
        $firstName = NameField::fromString('John');
        $userUuid = ResourceUuid::fromString('a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d');
        $resetToken = 'mock-reset-token-123';

        $request->expects(self::exactly(2))->method('firstName')->willReturn($firstName);
        $request->expects(self::once())->method('lastName')->willReturn(NameField::fromString('Doe'));
        $request->expects(self::once())->method('email')->willReturn($email);
        $request->expects(self::once())->method('phone')->willReturn(PhoneField::fromString('0612345678'));
        $request->expects(self::once())->method('position')->willReturn(NameField::fromString('Developer'));
        $request->expects(self::once())->method('department')->willReturn(NameField::fromString('IT'));
        $request->expects(self::once())->method('hiredAt')->willReturn(new \DateTimeImmutable('2024-01-15'));

        // Assert
        $employeeRepository->expects(self::once())
            ->method('emailExists')
            ->with($email)
            ->willReturn(false)
        ;

        $userCreatorGateway->expects(self::once())
            ->method('createUser')
            ->with(self::callback(static function (CreateUserDTO $dto) use ($email): bool {
                return $dto->email === $email
                    && $dto->plainPassword !== ''
                    && $dto->roles === ['ROLE_USER'];
            }))
            ->willReturn(new CreatedUserDTO($userUuid, $email))
        ;

        $passwordResetTokenCreator->expects(self::once())
            ->method('createResetToken')
            ->with($userUuid)
            ->willReturn($resetToken)
        ;

        $notificationGateway->expects(self::once())
            ->method('sendEmployeeWelcomeEmail')
            ->with($email, $firstName, $resetToken)
        ;

        $employeeRepository->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (Employee $employee) use ($userUuid): bool {
                return $employee->userUuid() === $userUuid
                    && $employee->contactInformation()->email->toString() === 'john.doe@example.com'
                    && $employee->contactInformation()->phone->toNumber() === '0612345678'
                    && $employee->position()->toString() === 'Developer'
                    && $employee->department()->toString() === 'IT'
                    && $employee->hiredAt()->format('Y-m-d') === '2024-01-15'
                    && $employee->isActive();
            }))
        ;

        // Act
        $response = $useCase->execute($request);
        $employee = $response->employee;

        // Assert
        self::assertSame('John', $employee->firstName()->toString());
        self::assertSame('Doe', $employee->lastName()->toString());
        self::assertSame($email->toString(), $employee->contactInformation()->email->toString());
        self::assertSame($userUuid->toString(), $employee->userUuid()->toString());
        self::assertTrue($employee->isActive());
    }

    public function testCreateEmployeeFailsWhenEmailAlreadyExistsInEmployeeRepository(): void
    {
        // Arrange
        $employeeRepository = $this->createMock(EmployeeRepository::class);
        $userCreatorGateway = $this->createMock(UserCreatorGateway::class);
        $passwordResetTokenCreator = $this->createMock(PasswordResetGateway::class);
        $notificationGateway = $this->createMock(NotificationGateway::class);
        $useCase = new CreateEmployee(
            $employeeRepository,
            $userCreatorGateway,
            $passwordResetTokenCreator,
            $notificationGateway
        );
        $request = $this->createMock(CreateEmployeeRequest::class);

        $email = EmailField::fromString('john.doe@example.com');

        // Assert
        $request->expects(self::never())->method('firstName')->willReturn(NameField::fromString('John'));
        $request->expects(self::never())->method('lastName')->willReturn(NameField::fromString('Doe'));
        $request->expects(self::once())->method('email')->willReturn($email);
        $request->expects(self::never())->method('phone')->willReturn(PhoneField::fromString('0612345678'));
        $request->expects(self::never())->method('position')->willReturn(NameField::fromString('Developer'));
        $request->expects(self::never())->method('department')->willReturn(NameField::fromString('IT'));
        $request->expects(self::never())->method('hiredAt')->willReturn(new \DateTimeImmutable('2024-01-15'));

        $employeeRepository->expects(self::once())
            ->method('emailExists')
            ->with($email)
            ->willReturn(true)
        ;

        $userCreatorGateway->expects(self::never())->method('createUser');
        $passwordResetTokenCreator->expects(self::never())->method('createResetToken');
        $notificationGateway->expects(self::never())->method('sendEmployeeWelcomeEmail');
        $employeeRepository->expects(self::never())->method('save');

        // Act
        $this->expectException(EmployeeAlreadyExists::class);
        $this->expectExceptionMessage(EmployeeAlreadyExists::MESSAGE);
        $useCase->execute($request);
    }

    public function testCreateEmployeeFailsWhenUserAlreadyExistsInAuthBc(): void
    {
        // Arrange
        $employeeRepository = $this->createMock(EmployeeRepository::class);
        $userCreatorGateway = $this->createMock(UserCreatorGateway::class);
        $passwordResetTokenCreator = $this->createMock(PasswordResetGateway::class);
        $notificationGateway = $this->createMock(NotificationGateway::class);
        $useCase = new CreateEmployee(
            $employeeRepository,
            $userCreatorGateway,
            $passwordResetTokenCreator,
            $notificationGateway
        );
        $request = $this->createMock(CreateEmployeeRequest::class);

        $email = EmailField::fromString('existing.user@example.com');

        // Assert
        $request->expects(self::never())->method('firstName')->willReturn(NameField::fromString('John'));
        $request->expects(self::never())->method('lastName')->willReturn(NameField::fromString('Doe'));
        $request->expects(self::once())->method('email')->willReturn($email);
        $request->expects(self::never())->method('phone')->willReturn(PhoneField::fromString('0612345678'));
        $request->expects(self::never())->method('position')->willReturn(NameField::fromString('Developer'));
        $request->expects(self::never())->method('department')->willReturn(NameField::fromString('IT'));
        $request->expects(self::never())->method('hiredAt')->willReturn(new \DateTimeImmutable('2024-01-15'));

        $employeeRepository->expects(self::once())
            ->method('emailExists')
            ->with($email)
            ->willReturn(false)
        ;

        $userCreatorGateway->expects(self::once())
            ->method('createUser')
            ->with(self::callback(static function (CreateUserDTO $dto) use ($email): bool {
                return $dto->email === $email;
            }))
            ->willThrowException(new UserEmailAlreadyExists($email))
        ;

        $passwordResetTokenCreator->expects(self::never())->method('createResetToken');
        $notificationGateway->expects(self::never())->method('sendEmployeeWelcomeEmail');
        $employeeRepository->expects(self::never())->method('save');

        $this->expectException(UserEmailAlreadyExists::class);
        $this->expectExceptionMessage(UserEmailAlreadyExists::MESSAGE);

        // Act
        $useCase->execute($request);
    }
}
