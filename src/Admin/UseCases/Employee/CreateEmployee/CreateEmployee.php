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

namespace Admin\UseCases\Employee\CreateEmployee;

use Admin\Entities\Employee\ContactInformation;
use Admin\Entities\Employee\Employee;
use Admin\Entities\Event\EmployeeWelcomeEmailRequested;
use Admin\Entities\Exception\Employee\EmployeeAlreadyExists;
use Admin\Entities\Repository\EmployeeRepository;
use Admin\UseCases\DTO\CreateUserDTO;
use Admin\UseCases\Gateway\EventPublisher;
use Admin\UseCases\Gateway\TransactionGateway;
use Admin\UseCases\Gateway\UserCreatorGateway;
use Shared\Entities\ResourceUuid;
use Shared\Entities\Role;

final readonly class CreateEmployee
{
    public function __construct(
        private EmployeeRepository $repository,
        private UserCreatorGateway $userCreatorGateway,
        private EventPublisher $eventPublisher,
        private TransactionGateway $transactionGateway,
    ) {
    }

    /**
     * Creates a new Employee with an associated User account.
     *
     * Email uniqueness is validated in 2 steps (Admin BC then Auth BC).
     * Under high concurrency, a race condition may occur — handled by PostgreSQL UNIQUE constraint.
     *
     * Welcome email is dispatched asynchronously after commit.
     * If sending fails, Employee/User creation is NOT rolled back.
     *
     * @throws EmployeeAlreadyExists
     */
    public function execute(CreateEmployeeRequest $request): CreateEmployeeResponse
    {
        /** @var Employee $employee */
        $employee = $this->transactionGateway->wrapInTransaction(
            operation: $this->createNewEmployee($request),
        );

        $this->eventPublisher->publish(
            new EmployeeWelcomeEmailRequested(
                employeeUuid: $employee->uuid(),
                employeeEmail: $employee->contactInformation()->email(),
                firstName: $employee->firstName()->toString(),
                userUuid: $employee->userUuid(),
            )
        );

        return new CreateEmployeeResponse($employee);
    }

    private function createNewEmployee(CreateEmployeeRequest $request): \Closure
    {
        return function () use ($request): Employee {
            $email = $request->email();
            /*
             * Note : Double validation intentionnelle (DDD pattern)
             * 1. Admin BC vérifie l'unicité dans son domaine (Employee)
             * 2. Auth BC vérifie l'unicité dans son domaine (User) via Gateway
             */
            if ($this->repository->emailExists($email)) {
                throw new EmployeeAlreadyExists($email);
            }

            $result = $this->userCreatorGateway->createUser(
                new CreateUserDTO(
                    email: $email,
                    plainPassword: bin2hex(random_bytes(16)),
                    roles: [Role::USER],
                )
            );

            $employee = Employee::create(
                uuid: ResourceUuid::generate(),
                firstName: $request->firstName(),
                lastName: $request->lastName(),
                contactInformation: ContactInformation::fromFields(
                    email: $email,
                    phone: $request->phone()
                ),
                position: $request->position(),
                department: $request->department(),
                hiredAt: $request->hiredAt(),
                userUuid: $result->uuid,
            );
            $this->repository->save($employee);

            return $employee;
        };
    }
}
