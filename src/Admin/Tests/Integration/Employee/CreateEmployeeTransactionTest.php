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

namespace Admin\Tests\Integration\Employee;

use Admin\Adapters\Controller\Symfony\Controller\Employee\CreateEmployee\CreateEmployeeApiRequest;
use Admin\Adapters\Gateway\ORM\Entity\Employee as EmployeeORM;
use Admin\Entities\Repository\EmployeeRepository;
use Admin\Tests\Factory\CompanyFactory;
use Admin\UseCases\Employee\CreateEmployee\CreateEmployee;
use Admin\UseCases\Gateway\NotificationGateway;
use Admin\UseCases\Gateway\PasswordResetGateway;
use Admin\UseCases\Gateway\TransactionGateway;
use Admin\UseCases\Gateway\UserCreatorGateway;
use Auth\Adapters\Gateway\ORM\Entity\User as UserORM;
use Doctrine\ORM\EntityManagerInterface;
use Shared\Entities\VO\EmailField;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\PhoneField;
use Shared\Tests\BaseFunctionalTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 *
 * @covers \Admin\UseCases\Employee\CreateEmployee\CreateEmployee
 */
final class CreateEmployeeTransactionTest extends BaseFunctionalTestCase
{
    use Factories;

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->entityManager = $entityManager;
    }

    public function testRollbackWhenPasswordResetTokenCreationFailsRevertsAll(): void
    {
        // Arrange
        CompanyFactory::createOne(['name' => 'Test company']);

        $passwordResetGateway = $this->createMock(PasswordResetGateway::class);

        $employeeRepository = self::getContainer()->get(EmployeeRepository::class);
        \assert($employeeRepository instanceof EmployeeRepository);

        $userCreatorGateway = self::getContainer()->get(UserCreatorGateway::class);
        \assert($userCreatorGateway instanceof UserCreatorGateway);

        $notificationGateway = self::getContainer()->get(NotificationGateway::class);
        \assert($notificationGateway instanceof NotificationGateway);

        $transactionGateway = self::getContainer()->get(TransactionGateway::class);
        \assert($transactionGateway instanceof TransactionGateway);

        $urlGenerator = self::getContainer()->get('router');
        \assert($urlGenerator instanceof UrlGeneratorInterface);

        $useCase = new CreateEmployee(
            repository: $employeeRepository,
            userCreatorGateway: $userCreatorGateway,
            passwordResetGateway: $passwordResetGateway,
            notificationGateway: $notificationGateway,
            transactionGateway: $transactionGateway,
            urlGenerator: $urlGenerator,
        );

        $request = new CreateEmployeeApiRequest(
            firstName: NameField::fromString('John'),
            lastName: NameField::fromString('Doe'),
            email: EmailField::fromString('test.rollback@example.com'),
            phone: PhoneField::fromString('0612345678'),
            position: NameField::fromString('Developer'),
            department: NameField::fromString('IT'),
            hiredAt: new \DateTimeImmutable('2024-01-15'),
        );

        // Assert
        $passwordResetGateway->expects(self::once())
            ->method('createResetToken')
            ->willThrowException(new \RuntimeException('Token creation failed'))
        ;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Token creation failed');

        // Act
        try {
            $useCase->execute($request);
        } finally {
            // Clear l'EntityManager pour forcer le reload depuis la base
            $this->entityManager->clear();

            // Assert
            $employeeCount = $this->entityManager
                ->getRepository(EmployeeORM::class)
                ->count([])
            ;
            self::assertSame(0, $employeeCount, 'Aucun Employee ne devrait être créé après rollback');

            $userCount = $this->entityManager
                ->getRepository(UserORM::class)
                ->count(['email' => 'test.rollback@example.com'])
            ;
            self::assertSame(0, $userCount, 'Aucun User ne devrait être créé après rollback');
        }
    }

    public function testSuccessfulTransactionCommitsAllChanges(): void
    {
        // Arrange
        CompanyFactory::createOne(['name' => 'Test company']);

        /** @var CreateEmployee $useCase */
        $useCase = self::getContainer()->get(CreateEmployee::class);

        $request = new CreateEmployeeApiRequest(
            firstName: NameField::fromString('Jane'),
            lastName: NameField::fromString('Smith'),
            email: EmailField::fromString('jane.smith@example.com'),
            phone: PhoneField::fromString('0687654321'),
            position: NameField::fromString('Manager'),
            department: NameField::fromString('Sales'),
            hiredAt: new \DateTimeImmutable('2024-02-01'),
        );

        // Act
        $response = $useCase->execute($request);

        // Clear l'EntityManager pour forcer le reload depuis la base
        $this->entityManager->clear();

        // Assert
        $employees = $this->entityManager
            ->getRepository(EmployeeORM::class)
            ->findAll()
        ;
        self::assertCount(1, $employees);

        /** @var EmployeeORM $employee */
        $employee = $employees[0];
        self::assertSame('Jane', $employee->firstName());
        self::assertSame('Smith', $employee->lastName());
        self::assertSame('jane.smith@example.com', $employee->email());

        $user = $this->entityManager
            ->getRepository(UserORM::class)
            ->findOneBy(['email' => 'jane.smith@example.com'])
        ;
        self::assertNotNull($user, 'Le User devrait être créé dans Auth BC');
        self::assertSame($response->employee->userUuid()->toString(), $user->uuid());
    }
}
