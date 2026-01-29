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
use Admin\Entities\VO\EmployeeStatus;
use Admin\UseCases\DTO\CreateUserDTO;
use Admin\UseCases\Employee\Exception\UserEmailAlreadyExists;
use Admin\UseCases\Gateway\NotificationGateway;
use Admin\UseCases\Gateway\PasswordResetGateway;
use Admin\UseCases\Gateway\UserCreatorGateway;
use Shared\Entities\ResourceUuid;

final readonly class CreateEmployee
{
    public function __construct(
        private EmployeeRepository $repository,
        private UserCreatorGateway $userCreatorGateway,
        private PasswordResetGateway $passwordResetGateway,
        private NotificationGateway $notificationGateway,
    ) {
    }

    /**
     * @throws EmployeeAlreadyExists
     * @throws UserEmailAlreadyExists
     */
    public function execute(CreateEmployeeRequest $request): CreateEmployeeResponse
    {
        $email = $request->email();

        if ($this->repository->emailExists($email)) {
            throw new EmployeeAlreadyExists($email);
        }

        $result = $this->userCreatorGateway->createUser(
            new CreateUserDTO(
                email: $email,
                plainPassword: bin2hex(random_bytes(16)),
                roles: ['ROLE_USER'],
            )
        );

        $resetToken = $this->passwordResetGateway->createResetToken($result->uuid);

        $this->notificationGateway->sendEmployeeWelcomeEmail(
            $email,
            $request->firstName(),
            $resetToken
        );

        $employee = Employee::create(
            uuid: ResourceUuid::generate(),
            firstName: $request->firstName(),
            lastName: $request->lastName(),
            contactInformation: new ContactInformation(
                email: $email,
                phone: $request->phone()
            ),
            position: $request->position(),
            department: $request->department(),
            hiredAt: $request->hiredAt(),
            status: EmployeeStatus::ACTIVE,
            userUuid: $result->uuid,
        );

        $this->repository->save($employee);

        return new CreateEmployeeResponse($employee);
    }
}
