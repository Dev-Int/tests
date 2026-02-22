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

namespace Auth\UseCases\User\CreateUser;

use Auth\Entities\Exception\EmailAlreadyExists;
use Auth\Entities\Repository\UserRepository;
use Auth\Entities\User;
use Auth\UseCases\Gateway\PasswordHasherGateway;
use Shared\Entities\ResourceUuid;

final readonly class CreateUser
{
    public function __construct(
        private UserRepository $userRepository,
        private PasswordHasherGateway $passwordHasher,
    ) {
    }

    public function execute(CreateUserRequest $request): CreateUserResponse
    {
        $email = $request->email();

        // emailExists() intentionally checks active AND disabled users.
        // Email is permanently non-recyclable per ADR-008 (identifiant de liaison unique).
        if ($this->userRepository->emailExists($email)) {
            throw new EmailAlreadyExists($request->email()->toString());
        }

        $hashedPassword = $this->passwordHasher->hashPassword($request->plainPassword());

        $user = User::create(
            ResourceUuid::generate(),
            $email,
            $hashedPassword,
            $request->roles(),
        );

        $this->userRepository->create($user);

        return new CreateUserResponse($user);
    }
}
