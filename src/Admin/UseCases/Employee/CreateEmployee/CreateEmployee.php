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
use Admin\Entities\Exception\Employee\EmployeeAlreadyExists;
use Admin\Entities\Repository\EmployeeRepository;
use Admin\UseCases\DTO\CreateUserDTO;
use Admin\UseCases\Employee\Exception\UserEmailAlreadyExists;
use Admin\UseCases\Gateway\EmailPayload;
use Admin\UseCases\Gateway\EmailType;
use Admin\UseCases\Gateway\NotificationGateway;
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
        private NotificationGateway $notificationGateway,
        private TransactionGateway $transactionGateway,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @throws EmployeeAlreadyExists
     * @throws UserEmailAlreadyExists
     */
    public function execute(CreateEmployeeRequest $request): CreateEmployeeResponse
    {
        return $this->transactionGateway->wrapInTransaction(
            operation: $this->createNewEmployee($request),
        );
    }

    public function createNewEmployee(CreateEmployeeRequest $request): \Closure
    {
        return function () use ($request): CreateEmployeeResponse {
            $email = $request->email();
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
            $this->notificationGateway->sendEmail(
                new EmailPayload(
                    to: $email,
                    type: EmailType::EMPLOYEE_WELCOME,
                    subject: 'Bienvenue - Créez votre mot de passe',
                    context: [
                        'firstName' => $request->firstName()->toString(),
                        'userEmail' => $email->toString(),
                        'resetUrl' => $resetUrl,
                    ],
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

            return new CreateEmployeeResponse($employee);
        };
    }
}
