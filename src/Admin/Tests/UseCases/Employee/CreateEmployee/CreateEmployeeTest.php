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
use Admin\Entities\Event\EmployeeWelcomeEmailRequested;
use Admin\Entities\Exception\Employee\EmployeeAlreadyExists;
use Admin\Entities\Repository\EmployeeRepository;
use Admin\UseCases\DTO\CreatedUserDTO;
use Admin\UseCases\DTO\CreateUserDTO;
use Admin\UseCases\Employee\CreateEmployee\CreateEmployee;
use Admin\UseCases\Employee\CreateEmployee\CreateEmployeeRequest;
use Admin\UseCases\Gateway\EventPublisher;
use Admin\UseCases\Gateway\TransactionGateway;
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
        $eventPublisher = $this->createMock(EventPublisher::class);
        $transactionGateway = $this->createMock(TransactionGateway::class);
        $request = $this->createMock(CreateEmployeeRequest::class);

        $useCase = new CreateEmployee(
            $employeeRepository,
            $userCreatorGateway,
            $eventPublisher,
            $transactionGateway,
        );
        $email = EmailField::fromString('john.doe@example.com');
        $firstName = NameField::fromString('John');
        $userUuid = ResourceUuid::fromString('a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d');

        // Assert
        $request->expects(self::once())->method('firstName')->willReturn($firstName); // entity only
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

        $eventPublisher->expects(self::once())
            ->method('publish')
            ->with(
                self::callback(static function (EmployeeWelcomeEmailRequested $event) use ($email, $firstName, $userUuid): bool {
                    return $event->employeeEmail->equals($email)
                        && $event->firstName === $firstName->toString()
                        && $event->userUuid->toString() === $userUuid->toString();
                })
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
        $eventPublisher = $this->createMock(EventPublisher::class);
        $transactionGateway = $this->createMock(TransactionGateway::class);
        $request = $this->createMock(CreateEmployeeRequest::class);

        $useCase = new CreateEmployee(
            $employeeRepository,
            $userCreatorGateway,
            $eventPublisher,
            $transactionGateway,
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
        $eventPublisher->expects(self::never())->method('publish');
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
        $eventPublisher = $this->createMock(EventPublisher::class);
        $transactionGateway = $this->createMock(TransactionGateway::class);
        $request = $this->createMock(CreateEmployeeRequest::class);

        $useCase = new CreateEmployee(
            $employeeRepository,
            $userCreatorGateway,
            $eventPublisher,
            $transactionGateway,
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
            ->willThrowException(new EmployeeAlreadyExists($email))
        ;
        $eventPublisher->expects(self::never())->method('publish');
        $employeeRepository->expects(self::never())->method('save');

        $this->expectException(EmployeeAlreadyExists::class);
        $this->expectExceptionMessage(EmployeeAlreadyExists::MESSAGE);

        // Act
        $useCase->execute($request);
    }

    public function testEventNotPublishedWhenTransactionFails(): void
    {
        // Arrange
        $employeeRepository = $this->createMock(EmployeeRepository::class);
        $userCreatorGateway = $this->createMock(UserCreatorGateway::class);
        $eventPublisher = $this->createMock(EventPublisher::class);
        $transactionGateway = $this->createMock(TransactionGateway::class);
        $request = $this->createMock(CreateEmployeeRequest::class);

        $useCase = new CreateEmployee(
            $employeeRepository,
            $userCreatorGateway,
            $eventPublisher,
            $transactionGateway,
        );

        // Simulate transaction failure
        $transactionGateway->expects(self::once())
            ->method('wrapInTransaction')
            ->willThrowException(new \RuntimeException('Transaction failed'))
        ;

        // Event should NOT be published if transaction fails
        $eventPublisher->expects(self::never())->method('publish');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Transaction failed');

        // Act
        $useCase->execute($request);
    }

    public function testGeneratesSecureTemporaryPassword(): void
    {
        // Arrange
        $employeeRepository = $this->createMock(EmployeeRepository::class);
        $userCreatorGateway = $this->createMock(UserCreatorGateway::class);
        $eventPublisher = $this->createMock(EventPublisher::class);
        $transactionGateway = $this->createMock(TransactionGateway::class);
        $request = $this->createMock(CreateEmployeeRequest::class);

        $useCase = new CreateEmployee(
            $employeeRepository,
            $userCreatorGateway,
            $eventPublisher,
            $transactionGateway,
        );
        $email = EmailField::fromString('test@example.com');
        $firstName = NameField::fromString('John');
        $lastName = NameField::fromString('Doe');
        $userUuid = ResourceUuid::generate();

        // Assert
        $request->expects(self::atLeastOnce())->method('firstName')->willReturn($firstName);
        $request->expects(self::atLeastOnce())->method('lastName')->willReturn($lastName);
        $request->expects(self::once())->method('email')->willReturn($email);
        $request->expects(self::once())->method('phone')->willReturn(PhoneField::fromString('0612345678'));
        $request->expects(self::atLeastOnce())->method('position')->willReturn(NameField::fromString('Developer'));
        $request->expects(self::atLeastOnce())->method('department')->willReturn(NameField::fromString('IT'));
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

        $eventPublisher->expects(self::once())
            ->method('publish')
            ->with(
                self::callback(static function (EmployeeWelcomeEmailRequested $event) use ($email, $firstName, $userUuid): bool {
                    return $event->employeeEmail->equals($email)
                        && $event->firstName === $firstName->toString()
                        && $event->userUuid->toString() === $userUuid->toString();
                })
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
