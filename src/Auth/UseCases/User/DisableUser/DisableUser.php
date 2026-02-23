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

namespace Auth\UseCases\User\DisableUser;

use Auth\Entities\Repository\UserRepository;

final readonly class DisableUser
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function execute(DisableUserRequest $request): DisableUserResponse
    {
        $user = $this->userRepository->getByUuid($request->uuid());

        $user->disable();

        $this->userRepository->update($user);

        return new DisableUserResponse($user);
    }
}
