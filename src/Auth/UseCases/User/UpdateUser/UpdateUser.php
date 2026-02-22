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

use Auth\Entities\Repository\UserRepository;
use Auth\UseCases\Gateway\PasswordHasherGateway;

final readonly class UpdateUser
{
    public function __construct(
        private UserRepository $userRepository,
        private PasswordHasherGateway $passwordHasher,
    ) {
    }

    public function execute(UpdateUserRequest $request): UpdateUserResponse
    {
        $user = $this->userRepository->getByUuid($request->uuid());

        if ($request->plainPassword() !== null) {
            $hashedPassword = $this->passwordHasher->hashPassword($request->plainPassword());
            $user->changePassword($hashedPassword);
        }

        if ($request->roles() !== null) {
            $user->updateRoles($request->roles());
        }

        $this->userRepository->update($user);

        return new UpdateUserResponse($user);
    }
}
