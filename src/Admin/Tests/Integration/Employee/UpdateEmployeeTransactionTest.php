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

use Admin\Adapters\Controller\Symfony\Controller\Employee\UpdateEmployee\UpdateEmployeeApiRequest;
use Admin\Adapters\Gateway\ORM\Entity\Employee as EmployeeORM;
use Admin\Entities\Employee\Employee;
use Admin\Entities\Repository\EmployeeRepository;
use Admin\Tests\Factory\CompanyFactory;
use Admin\Tests\Factory\EmployeeFactory;
use Admin\UseCases\Employee\UpdateEmployee\UpdateEmployee;
use Admin\UseCases\Gateway\TransactionGateway;
use Doctrine\ORM\EntityManagerInterface;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\PhoneField;
use Shared\Tests\BaseFunctionalTestCase;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 *
 * @covers \Admin\UseCases\Employee\UpdateEmployee\UpdateEmployee
 */
final class UpdateEmployeeTransactionTest extends BaseFunctionalTestCase
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

    public function testRollbackWhenUpdateFailsRevertsAllChanges(): void
    {
        // Arrange - Create company and employee
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
        $originalPhone = '0612345678';
        $newPhone = PhoneField::fromString('0687654321');

        // Clear EntityManager to ensure fresh state
        $this->entityManager->clear();

        // Create a mock repository that throws exception on update
        $repositoryMock = $this->createMock(EmployeeRepository::class);
        $realRepository = self::getContainer()->get(EmployeeRepository::class);
        \assert($realRepository instanceof EmployeeRepository);

        // getByUuid() returns real employee
        $repositoryMock->expects(self::once())
            ->method('getByUuid')
            ->with($employeeUuid)
            ->willReturnCallback(static fn () => $realRepository->getByUuid($employeeUuid))
        ;

        // update() throws exception to simulate failure
        $repositoryMock->expects(self::once())
            ->method('update')
            ->willThrowException(new \RuntimeException('Database error during update'))
        ;

        $transactionGateway = self::getContainer()->get(TransactionGateway::class);
        \assert($transactionGateway instanceof TransactionGateway);

        $useCase = new UpdateEmployee(
            repository: $repositoryMock,
            transactionGateway: $transactionGateway,
        );

        $request = new UpdateEmployeeApiRequest(
            uuid: $employeeUuid,
            phone: $newPhone,
            position: NameField::fromString('Developer'),
            department: NameField::fromString('IT'),
        );

        // Assert exception is thrown
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Database error during update');

        // Act
        try {
            $useCase->execute($request);
        } finally {
            // Clear EntityManager to force reload from database
            $this->entityManager->clear();

            // Assert - Phone should NOT be updated (rollback occurred)
            /** @var EmployeeORM|null $employee */
            $employee = $this->entityManager
                ->getRepository(EmployeeORM::class)
                ->findOneBy(['uuid' => $employeeUuid->toString()])
            ;

            self::assertNotNull($employee, 'Employee should still exist after rollback');
            self::assertSame($originalPhone, $employee->phone(), 'Phone should NOT be updated after rollback');
        }
    }

    public function testSuccessfulTransactionCommitsAllChanges(): void
    {
        // Arrange - Create company and employee
        CompanyFactory::createOne(['name' => 'Test company']);

        $employeeProxy = EmployeeFactory::createOne([
            'firstName' => 'Jane',
            'lastName' => 'Smith',
            'email' => 'jane.smith@test.com',
            'phone' => '0612345678',
            'position' => 'Developer',
            'department' => 'IT',
        ]);

        $employeeUuid = ResourceUuid::fromString($employeeProxy->uuid());

        // Clear EntityManager
        $this->entityManager->clear();

        /** @var UpdateEmployee $useCase */
        $useCase = self::getContainer()->get(UpdateEmployee::class);

        $request = new UpdateEmployeeApiRequest(
            uuid: $employeeUuid,
            phone: PhoneField::fromString('0687654321'),
            position: NameField::fromString('Senior Developer'),
            department: NameField::fromString('Engineering'),
        );

        // Act
        $response = $useCase->execute($request);

        // Clear EntityManager to force reload from database
        $this->entityManager->clear();

        // Assert - All changes should be persisted
        /** @var EmployeeORM|null $employee */
        $employee = $this->entityManager
            ->getRepository(EmployeeORM::class)
            ->findOneBy(['uuid' => $employeeUuid->toString()])
        ;

        self::assertNotNull($employee);
        self::assertSame('0687654321', $employee->phone());
        self::assertSame('Senior Developer', $employee->position());
        self::assertSame('Engineering', $employee->department());

        // Verify response matches
        self::assertSame($employeeUuid->toString(), $response->employee()->uuid()->toString());
        self::assertSame('0687654321', $response->employee()->contactInformation()->phone()->toNumber());
    }
}
