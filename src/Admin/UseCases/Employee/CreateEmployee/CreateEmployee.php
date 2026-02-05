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
use Admin\Entities\Exception\Employee\EmployeeEmailAlreadyExists;
use Admin\Entities\Exception\InvalidResetUrlException;
use Admin\Entities\Repository\EmployeeRepository;
use Admin\UseCases\DTO\CreateUserDTO;
use Admin\UseCases\Gateway\EventPublisher;
use Admin\UseCases\Gateway\PasswordResetGateway;
use Admin\UseCases\Gateway\TransactionGateway;
use Admin\UseCases\Gateway\UserCreatorGateway;
use Shared\Entities\ResourceUuid;
use Shared\Entities\Role;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class CreateEmployee
{
    public function __construct(
        private EmployeeRepository $repository,
        private UserCreatorGateway $userCreatorGateway,
        private PasswordResetGateway $passwordResetGateway,
        private EventPublisher $eventPublisher,
        private TransactionGateway $transactionGateway,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * Creates a new Employee with an associated User account.
     *
     * Note: Email uniqueness is validated in 2 steps (Admin BC then Auth BC).
     * Under high concurrency, a race condition might occur between the check and User creation.
     * In this case, Auth BC will throw the EmployeeAlreadyExists exception
     * (caught from PostgreSQL UNIQUE constraint on users.email).
     *
     * This behavior is acceptable as:
     * - Employee creation is not a high-frequency operation
     * - PostgreSQL ensures final consistency via UNIQUE constraint
     * - The error is properly reported to the caller
     *
     * Welcome email is sent asynchronously AFTER transaction commits:
     * - If email sending fails, Employee/User are still created (no rollback)
     * - a Messenger retry mechanism handles temporary SMTP failures
     * - Failed messages are stored in failed transport for manual analysis
     *
     * @throws EmployeeAlreadyExists
     * @throws EmployeeEmailAlreadyExists
     * @throws InvalidResetUrlException
     */
    public function execute(CreateEmployeeRequest $request): CreateEmployeeResponse
    {
        $response = $this->transactionGateway->wrapInTransaction(
            operation: $this->createNewEmployee($request, $resetUrl),
        );
        if ($resetUrl === null) {
            throw new InvalidResetUrlException();
        }

        $this->eventPublisher->publish(
            new EmployeeWelcomeEmailRequested(
                employeeUuid: $response->employee->uuid(),
                employeeEmail: $response->employee->contactInformation()->email(),
                firstName: $response->employee->firstName()->toString(),
                resetUrl: $resetUrl,
            )
        );

        return $response;
    }

    public function createNewEmployee(CreateEmployeeRequest $request, ?string &$resetUrl): \Closure
    {
        return function () use ($request, &$resetUrl): CreateEmployeeResponse {
            $email = $request->email();
            /*
             * Note : Double validation intentionnelle (DDD pattern)
             * 1. Admin BC vérifie l'unicité dans son domaine (Employee)
             * 2. Auth BC vérifie l'unicité dans son domaine (User) via Gateway
             * Transaction garantit l'atomicité - pas de race condition possible
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
            $resetToken = $this->passwordResetGateway->createResetToken($result->uuid);
            $resetUrl = $this->urlGenerator->generate(
                'auth_password_reset',
                ['token' => $resetToken],
                UrlGeneratorInterface::ABSOLUTE_URL
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

            return new CreateEmployeeResponse($employee);
        };
    }
}
