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
use Auth\Entities\VO\HashedPassword;
use Shared\Entities\ResourceUuid;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class CreateUser
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function execute(CreateUserRequest $request): CreateUserResponse
    {
        $email = $request->email();

        if ($this->userRepository->emailExists($email)) {
            throw new EmailAlreadyExists($request->email()->toString());
        }

        // Create a temporary user to hash the password
        $hashedPassword = $this->passwordHasher->hashPassword(
            new PasswordHasherUser(),
            $request->plainPassword()
        );

        $user = User::create(
            ResourceUuid::generate(),
            $email,
            HashedPassword::fromHash($hashedPassword),
            $request->roles(),
        );

        $this->userRepository->create($user);

        return new CreateUserResponse($user);
    }
}
