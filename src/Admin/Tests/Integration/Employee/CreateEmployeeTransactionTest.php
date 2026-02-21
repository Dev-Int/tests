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
use Admin\Tests\Factory\CompanyFactory;
use Admin\UseCases\Employee\CreateEmployee\CreateEmployee;
use Auth\Adapters\Gateway\ORM\Entity\User as UserORM;
use Doctrine\ORM\EntityManagerInterface;
use Shared\Entities\VO\EmailField;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\PhoneField;
use Shared\Tests\BaseFunctionalTestCase;
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

        // Assert
        $this->entityManager->clear();
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
