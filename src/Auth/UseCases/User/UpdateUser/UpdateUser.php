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

namespace Auth\UseCases\User\UpdateUser;

use Auth\Entities\Exception\EmailAlreadyExists;
use Auth\Entities\Repository\UserRepository;
use Auth\Entities\VO\HashedPassword;
use Auth\UseCases\User\CreateUser\PasswordHasherUser;
use Shared\Entities\VO\EmailField;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class UpdateUser
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function execute(UpdateUserRequest $request): UpdateUserResponse
    {
        $user = $this->userRepository->getByUuid($request->uuid());

        if (($email = $request->email()) instanceof EmailField) {
            if (
                !$user->email()->equals($email)
                && $this->userRepository->emailExists($email)
            ) {
                throw new EmailAlreadyExists($email->toString());
            }
            $user->changeEmail($email);
        }

        if ($request->plainPassword() !== null) {
            $hashedPassword = $this->passwordHasher->hashPassword(
                new PasswordHasherUser(),
                $request->plainPassword()
            );
            $user->changePassword(HashedPassword::fromHash($hashedPassword));
        }

        if ($request->roles() !== null) {
            $user->updateRoles($request->roles());
        }

        $this->userRepository->update($user);

        return new UpdateUserResponse($user);
    }
}
