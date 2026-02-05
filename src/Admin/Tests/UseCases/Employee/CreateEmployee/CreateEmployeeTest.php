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
use Admin\UseCases\Gateway\EmailPayload;
use Admin\UseCases\Gateway\EmailType;
use Admin\UseCases\Gateway\NotificationGateway;
use Admin\UseCases\Gateway\PasswordResetGateway;
use Admin\UseCases\Gateway\TransactionGateway;
use Admin\UseCases\Gateway\UserCreatorGateway;
use PHPUnit\Framework\TestCase;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\EmailField;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\PhoneField;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
        $transactionGateway = $this->createMock(TransactionGateway::class);
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $request = $this->createMock(CreateEmployeeRequest::class);

        $useCase = new CreateEmployee(
            $employeeRepository,
            $userCreatorGateway,
            $passwordResetTokenCreator,
            $notificationGateway,
            $transactionGateway,
            $urlGenerator
        );
        $email = EmailField::fromString('john.doe@example.com');
        $firstName = NameField::fromString('John');
        $userUuid = ResourceUuid::fromString('a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d');
        $resetToken = 'mock-reset-token-123';

        // Assert
        $request->expects(self::exactly(2))->method('firstName')->willReturn($firstName);
        $request->expects(self::once())->method('lastName')->willReturn(NameField::fromString('Doe'));
        $request->expects(self::once())->method('email')->willReturn($email);
        $request->expects(self::once())->method('phone')->willReturn(PhoneField::fromString('0612345678'));
        $request->expects(self::once())->method('position')->willReturn(NameField::fromString('Developer'));
        $request->expects(self::once())->method('department')->willReturn(NameField::fromString('IT'));
        $request->expects(self::once())->method('hiredAt')->willReturn(new \DateTimeImmutable('2024-01-15'));

        $transactionGateway->expects(self::once())
            ->method('wrapInTransaction')
            ->willReturnCallback(static fn (callable $func) => $func())
        ;

        $employeeRepository->expects(self::once())->method('emailExists')->with($email)->willReturn(false);
        $userCreatorGateway->expects(self::once())
            ->method('createUser')
            ->willReturn(new CreatedUserDTO($userUuid, $email))
        ;
        $passwordResetTokenCreator->expects(self::once())
            ->method('createResetToken')
            ->with($userUuid)
            ->willReturn($resetToken)
        ;
        $urlGenerator->expects(self::once())
            ->method('generate')
            ->with(
                'auth_password_reset',
                ['token' => $resetToken],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn('https://example.com/reset/' . $resetToken)
        ;
        $notificationGateway->expects(self::once())
            ->method('sendEmail')
            ->with(
                new EmailPayload(
                    to: $email,
                    type: EmailType::EMPLOYEE_WELCOME,
                    subject: 'Bienvenue - Créez votre mot de passe',
                    context: [
                        'firstName' => $firstName->toString(),
                        'userEmail' => $email->toString(),
                        'resetUrl' => 'https://example.com/reset/' . $resetToken,
                    ],
                )
            )
        ;
        $employeeRepository->expects(self::once())
            ->method('save')
            ->with(
                self::callback(static function (Employee $employee) use ($userUuid): bool {
                    return $employee->userUuid() === $userUuid
                        && $employee->contactInformation()->phone()->toNumber() === '0612345678'
                        && $employee->position()->toString() === 'Developer'
                        && $employee->department()->toString() === 'IT'
                        && $employee->hiredAt()->format('Y-m-d') === '2024-01-15'
                        && $employee->isActive();
                })
            )
        ;

        // Act
        $response = $useCase->execute($request);
        $employee = $response->employee;

        // Assert
        self::assertSame('John', $employee->firstName()->toString());
        self::assertSame('Doe', $employee->lastName()->toString());
        self::assertSame($email->toString(), $employee->contactInformation()->email()->toString());
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
        $transactionGateway = $this->createMock(TransactionGateway::class);
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $request = $this->createMock(CreateEmployeeRequest::class);

        $useCase = new CreateEmployee(
            $employeeRepository,
            $userCreatorGateway,
            $passwordResetTokenCreator,
            $notificationGateway,
            $transactionGateway,
            $urlGenerator
        );
        $email = EmailField::fromString('john.doe@example.com');

        // Assert
        $request->expects(self::never())->method('firstName')->willReturn(NameField::fromString('John'));
        $request->expects(self::never())->method('lastName')->willReturn(NameField::fromString('Doe'));
        $request->expects(self::once())->method('email')->willReturn($email);
        $request->expects(self::never())->method('phone')->willReturn(PhoneField::fromString('0612345678'));
        $request->expects(self::never())->method('position')->willReturn(NameField::fromString('Developer'));
        $request->expects(self::never())->method('department')->willReturn(NameField::fromString('IT'));
        $request->expects(self::never())->method('hiredAt')->willReturn(new \DateTimeImmutable('2024-01-15'));

        $transactionGateway->expects(self::once())
            ->method('wrapInTransaction')
            ->willReturnCallback(static fn (callable $func) => $func())
        ;
        $employeeRepository->expects(self::once())->method('emailExists')->with($email)->willReturn(true);
        $userCreatorGateway->expects(self::never())->method('createUser');
        $passwordResetTokenCreator->expects(self::never())->method('createResetToken');
        $notificationGateway->expects(self::never())->method('sendEmail');
        $employeeRepository->expects(self::never())->method('save');

        $this->expectException(EmployeeAlreadyExists::class);
        $this->expectExceptionMessage(EmployeeAlreadyExists::MESSAGE);

        // Act
        $useCase->execute($request);
    }

    public function testCreateEmployeeFailsWhenUserAlreadyExistsInAuthBc(): void
    {
        // Arrange
        $employeeRepository = $this->createMock(EmployeeRepository::class);
        $userCreatorGateway = $this->createMock(UserCreatorGateway::class);
        $passwordResetTokenCreator = $this->createMock(PasswordResetGateway::class);
        $notificationGateway = $this->createMock(NotificationGateway::class);
        $transactionGateway = $this->createMock(TransactionGateway::class);
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $request = $this->createMock(CreateEmployeeRequest::class);

        $useCase = new CreateEmployee(
            $employeeRepository,
            $userCreatorGateway,
            $passwordResetTokenCreator,
            $notificationGateway,
            $transactionGateway,
            $urlGenerator
        );
        $email = EmailField::fromString('existing.user@example.com');

        // Assert
        $request->expects(self::never())->method('firstName')->willReturn(NameField::fromString('John'));
        $request->expects(self::never())->method('lastName')->willReturn(NameField::fromString('Doe'));
        $request->expects(self::once())->method('email')->willReturn($email);
        $request->expects(self::never())->method('phone')->willReturn(PhoneField::fromString('0612345678'));
        $request->expects(self::never())->method('position')->willReturn(NameField::fromString('Developer'));
        $request->expects(self::never())->method('department')->willReturn(NameField::fromString('IT'));
        $request->expects(self::never())->method('hiredAt')->willReturn(new \DateTimeImmutable('2024-01-15'));

        $transactionGateway->expects(self::once())
            ->method('wrapInTransaction')
            ->willReturnCallback(static fn (callable $func) => $func())
        ;
        $employeeRepository->expects(self::once())->method('emailExists')->with($email)->willReturn(false);
        $userCreatorGateway->expects(self::once())
            ->method('createUser')
            ->willThrowException(new UserEmailAlreadyExists($email))
        ;
        $passwordResetTokenCreator->expects(self::never())->method('createResetToken');
        $notificationGateway->expects(self::never())->method('sendEmail');
        $employeeRepository->expects(self::never())->method('save');

        $this->expectException(UserEmailAlreadyExists::class);
        $this->expectExceptionMessage(UserEmailAlreadyExists::MESSAGE);

        // Act
        $useCase->execute($request);
    }

    public function testRollbackWhenResetTokenCreationFails(): void
    {
        // Arrange
        $employeeRepository = $this->createMock(EmployeeRepository::class);
        $userCreatorGateway = $this->createMock(UserCreatorGateway::class);
        $passwordResetTokenCreator = $this->createMock(PasswordResetGateway::class);
        $notificationGateway = $this->createMock(NotificationGateway::class);
        $transactionGateway = $this->createMock(TransactionGateway::class);
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $request = $this->createMock(CreateEmployeeRequest::class);

        $useCase = new CreateEmployee(
            $employeeRepository,
            $userCreatorGateway,
            $passwordResetTokenCreator,
            $notificationGateway,
            $transactionGateway,
            $urlGenerator
        );
        $email = EmailField::fromString('test@example.com');
        $userUuid = ResourceUuid::generate();

        // Assert
        $request->expects(self::once())->method('email')->willReturn($email);
        $request->expects(self::never())->method('firstName');
        $request->expects(self::never())->method('lastName');
        $request->expects(self::never())->method('phone');
        $request->expects(self::never())->method('position');
        $request->expects(self::never())->method('department');
        $request->expects(self::never())->method('hiredAt');

        $transactionGateway->expects(self::once())
            ->method('wrapInTransaction')
            ->willReturnCallback(static fn (callable $func) => $func())
        ;
        $employeeRepository->expects(self::once())->method('emailExists')->with($email)->willReturn(false);
        $userCreatorGateway->expects(self::once())
            ->method('createUser')
            ->willReturn(new CreatedUserDTO($userUuid, $email))
        ;

        $passwordResetTokenCreator->expects(self::once())
            ->method('createResetToken')
            ->with($userUuid)
            ->willThrowException(new \RuntimeException('Token creation failed'))
        ;
        $notificationGateway->expects(self::never())->method('sendEmail');
        $employeeRepository->expects(self::never())->method('save');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Token creation failed');

        // Act
        $useCase->execute($request);
    }

    public function testRollbackWhenEmailSendingFails(): void
    {
        // Arrange
        $employeeRepository = $this->createMock(EmployeeRepository::class);
        $userCreatorGateway = $this->createMock(UserCreatorGateway::class);
        $passwordResetTokenCreator = $this->createMock(PasswordResetGateway::class);
        $notificationGateway = $this->createMock(NotificationGateway::class);
        $transactionGateway = $this->createMock(TransactionGateway::class);
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $request = $this->createMock(CreateEmployeeRequest::class);

        $useCase = new CreateEmployee(
            $employeeRepository,
            $userCreatorGateway,
            $passwordResetTokenCreator,
            $notificationGateway,
            $transactionGateway,
            $urlGenerator
        );
        $email = EmailField::fromString('test@example.com');
        $firstName = NameField::fromString('John');
        $userUuid = ResourceUuid::generate();
        $resetToken = 'test-token';

        // Assert
        $request->expects(self::once())->method('firstName')->willReturn($firstName);
        $request->expects(self::once())->method('email')->willReturn($email);
        $request->expects(self::never())->method('lastName');
        $request->expects(self::never())->method('phone');
        $request->expects(self::never())->method('position');
        $request->expects(self::never())->method('department');
        $request->expects(self::never())->method('hiredAt');

        $transactionGateway->expects(self::once())
            ->method('wrapInTransaction')
            ->willReturnCallback(static fn (callable $func) => $func())
        ;
        $employeeRepository->expects(self::once())->method('emailExists')->with($email)->willReturn(false);
        $userCreatorGateway->expects(self::once())
            ->method('createUser')
            ->willReturn(new CreatedUserDTO($userUuid, $email))
        ;
        $passwordResetTokenCreator->expects(self::once())
            ->method('createResetToken')
            ->with($userUuid)
            ->willReturn($resetToken)
        ;
        $urlGenerator->expects(self::once())
            ->method('generate')
            ->with(
                'auth_password_reset',
                ['token' => $resetToken],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn('https://example.com/reset/' . $resetToken)
        ;

        $notificationGateway->expects(self::once())
            ->method('sendEmail')
            ->willThrowException(new \RuntimeException('Email sending failed'))
        ;

        $employeeRepository->expects(self::never())->method('save');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Email sending failed');

        // Act
        $useCase->execute($request);
    }

    public function testGeneratesSecureTemporaryPassword(): void
    {
        // Arrange
        $employeeRepository = $this->createMock(EmployeeRepository::class);
        $userCreatorGateway = $this->createMock(UserCreatorGateway::class);
        $passwordResetTokenCreator = $this->createMock(PasswordResetGateway::class);
        $notificationGateway = $this->createMock(NotificationGateway::class);
        $transactionGateway = $this->createMock(TransactionGateway::class);
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $request = $this->createMock(CreateEmployeeRequest::class);

        $useCase = new CreateEmployee(
            $employeeRepository,
            $userCreatorGateway,
            $passwordResetTokenCreator,
            $notificationGateway,
            $transactionGateway,
            $urlGenerator
        );
        $email = EmailField::fromString('test@example.com');
        $firstName = NameField::fromString('John');
        $lastName = NameField::fromString('Doe');
        $userUuid = ResourceUuid::generate();
        $resetToken = 'secure-token';
        $resetUrl = 'https://example.com/reset/' . $resetToken;

        // Assert
        $request->expects(self::exactly(2))->method('firstName')->willReturn($firstName);
        $request->expects(self::once())->method('lastName')->willReturn($lastName);
        $request->expects(self::once())->method('email')->willReturn($email);
        $request->expects(self::once())->method('phone')->willReturn(PhoneField::fromString('0612345678'));
        $request->expects(self::once())->method('position')->willReturn(NameField::fromString('Developer'));
        $request->expects(self::once())->method('department')->willReturn(NameField::fromString('IT'));
        $request->expects(self::once())->method('hiredAt')->willReturn(new \DateTimeImmutable('2024-01-15'));

        $transactionGateway->expects(self::once())
            ->method('wrapInTransaction')
            ->willReturnCallback(static fn (callable $func) => $func())
        ;
        $employeeRepository->expects(self::once())->method('emailExists')->willReturn(false);

        $capturedPassword = null;
        $userCreatorGateway->expects(self::once())
            ->method('createUser')
            ->with(self::callback(static function (CreateUserDTO $dto) use (&$capturedPassword): bool {
                $capturedPassword = $dto->plainPassword;

                return true;
            }))
            ->willReturn(new CreatedUserDTO($userUuid, $email))
        ;
        $passwordResetTokenCreator->expects(self::once())
            ->method('createResetToken')
            ->with($userUuid)
            ->willReturn($resetToken)
        ;
        $urlGenerator->expects(self::once())
            ->method('generate')
            ->with(
                'auth_password_reset',
                ['token' => $resetToken],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn($resetUrl)
        ;
        $notificationGateway->expects(self::once())
            ->method('sendEmail')
            ->with(
                new EmailPayload(
                    to: $email,
                    type: EmailType::EMPLOYEE_WELCOME,
                    subject: 'Bienvenue - Créez votre mot de passe',
                    context: [
                        'firstName' => $firstName->toString(),
                        'resetUrl' => $resetUrl,
                        'userEmail' => $email->toString(),
                    ]
                )
            )
        ;
        $employeeRepository->expects(self::once())->method('save');

        // Act
        $useCase->execute($request);

        // Assert
        self::assertNotNull($capturedPassword, 'Le mot de passe doit être généré');
        self::assertGreaterThanOrEqual(
            32,
            \strlen($capturedPassword),
            'Le mot de passe doit faire au moins 32 caractères (16 bytes en hex)'
        );
        self::assertMatchesRegularExpression(
            '/^[0-9a-f]+$/',
            $capturedPassword,
            'Le mot de passe doit être un hash hexadécimal'
        );
    }
}
