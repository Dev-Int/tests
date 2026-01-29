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

namespace Auth\Adapters\Contracts\Services\CommandHandler\UpdateUser;

use Auth\Contracts\Exception\EmailAlreadyExists;
use Auth\Contracts\Services\CommandHandler\UpdateUser\UpdatedUserResult;
use Auth\Contracts\Services\CommandHandler\UpdateUser\UpdateUserCommand;
use Auth\Contracts\Services\CommandHandler\UpdateUser\UpdateUserCommandHandler as UserUpdaterContract;
use Auth\Entities\Exception\EmailAlreadyExists as DomainEmailAlreadyExists;
use Auth\Entities\Role;
use Auth\UseCases\User\UpdateUser\UpdateUser;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(UserUpdaterContract::class)]
final readonly class UpdateUserCommandHandler implements UserUpdaterContract
{
    public function __construct(
        private UpdateUser $updateUserUseCase,
    ) {
    }

    public function updateUser(UpdateUserCommand $command): UpdatedUserResult
    {
        try {
            $response = $this->updateUserUseCase->execute(
                new InternalUpdateUserRequest($command)
            );

            $roles = array_map(
                static fn (Role $role) => $role->value,
                $response->user->roles(),
            );

            return new UpdatedUserResult(
                uuid: $response->user->uuid()->toString(),
                email: $response->user->email()->toString(),
                roles: $roles,
            );
        } catch (DomainEmailAlreadyExists) {
            throw new EmailAlreadyExists($command->email);
        }
    }
}
