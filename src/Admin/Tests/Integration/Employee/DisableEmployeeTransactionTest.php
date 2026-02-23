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
use Admin\Adapters\Controller\Symfony\Controller\Employee\DisableEmployee\DisableEmployeeApiRequest;
use Admin\Adapters\Gateway\ORM\Entity\Employee as EmployeeORM;
use Admin\Entities\Repository\EmployeeRepository;
use Admin\Tests\Factory\CompanyFactory;
use Admin\Tests\Factory\EmployeeFactory;
use Admin\UseCases\Employee\CreateEmployee\CreateEmployee;
use Admin\UseCases\Employee\DisableEmployee\DisableEmployee;
use Admin\UseCases\Gateway\TransactionGateway;
use Admin\UseCases\Gateway\UserDisablerGateway;
use Auth\Adapters\Gateway\ORM\Entity\User as UserORM;
use Doctrine\ORM\EntityManagerInterface;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\EmailField;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\PhoneField;
use Shared\Tests\BaseFunctionalTestCase;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 *
 * @covers \Admin\UseCases\Employee\DisableEmployee\DisableEmployee
 */
final class DisableEmployeeTransactionTest extends BaseFunctionalTestCase
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

    public function testRollbackWhenUserDisablerFailsRevertsAllChanges(): void
    {
        // Arrange
        $userDisablerMock = $this->createMock(UserDisablerGateway::class);
        $transactionGateway = self::getContainer()->get(TransactionGateway::class);
        \assert($transactionGateway instanceof TransactionGateway);

        $repository = self::getContainer()->get(EmployeeRepository::class);
        \assert($repository instanceof EmployeeRepository);

        CompanyFactory::createOne(['name' => 'Test company']);
        $employeeProxy = EmployeeFactory::createOne([
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@test.com',
            'phone' => '0612345678',
            'position' => 'Developer',
            'department' => 'IT',
        ]);
        $employeeUuid = ResourceUuid::fromString($employeeProxy->uuid());

        $useCase = new DisableEmployee(
            transactionGateway: $transactionGateway,
            repository: $repository,
            userDisabler: $userDisablerMock,
        );
        $this->entityManager->clear();

        // Assert
        $userDisablerMock->expects(self::once())
            ->method('disableUser')
            ->willThrowException(new \RuntimeException('Auth service unavailable'))
        ;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Auth service unavailable');

        // Act
        try {
            $useCase->execute(new DisableEmployeeApiRequest($employeeUuid));
        } finally {
            // Assert
            $this->entityManager->clear();

            /** @var EmployeeORM|null $employeeOrm */
            $employeeOrm = $this->entityManager
                ->getRepository(EmployeeORM::class)
                ->findOneBy(['uuid' => $employeeUuid->toString()])
            ;

            self::assertNotNull($employeeOrm, 'Employee doit exister après rollback');
            self::assertNull(
                $employeeOrm->toDomain()->disabledAt(),
                'disabledAt doit être null après rollback'
            );
        }
    }

    public function testSuccessfulTransactionCommitsAllChanges(): void
    {
        // Arrange
        CompanyFactory::createOne(['name' => 'Test company']);

        /** @var CreateEmployee $createUseCase */
        $createUseCase = self::getContainer()->get(CreateEmployee::class);

        /** @var DisableEmployee $disableUseCase */
        $disableUseCase = self::getContainer()->get(DisableEmployee::class);

        $createResponse = $createUseCase->execute(new CreateEmployeeApiRequest(
            firstName: NameField::fromString('Jane'),
            lastName: NameField::fromString('Smith'),
            email: EmailField::fromString('jane.smith@example.com'),
            phone: PhoneField::fromString('0687654321'),
            position: NameField::fromString('Manager'),
            department: NameField::fromString('Sales'),
            hiredAt: new \DateTimeImmutable('2024-02-01'),
        ));

        $employeeUuid = $createResponse->employee->uuid();
        $userUuid = $createResponse->employee->userUuid();

        $this->entityManager->clear();

        // Act
        $disableUseCase->execute(new DisableEmployeeApiRequest($employeeUuid));

        // Assert
        $this->entityManager->clear();

        /** @var EmployeeORM|null $employeeOrm */
        $employeeOrm = $this->entityManager
            ->getRepository(EmployeeORM::class)
            ->findOneBy(['uuid' => $employeeUuid->toString()])
        ;

        self::assertNotNull($employeeOrm, 'Employee doit exister');
        self::assertNotNull(
            $employeeOrm->toDomain()->disabledAt(),
            'disabledAt ne doit pas être null après désactivation'
        );

        /** @var UserORM|null $userOrm */
        $userOrm = $this->entityManager
            ->getRepository(UserORM::class)
            ->findOneBy(['uuid' => $userUuid->toString()])
        ;

        self::assertNotNull($userOrm, 'User Auth doit exister');
        self::assertNotNull(
            $userOrm->toDomain()->disabledAt(),
            'User Auth doit être désactivé après commit cross-BC'
        );
    }
}
