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

namespace Auth\Adapters\Contracts\Services\Creator\User;

use Auth\Contracts\Exception\EmailAlreadyExists;
use Auth\Contracts\Services\Creator\User\CreatedUserResult;
use Auth\Contracts\Services\Creator\User\CreateUserCommand;
use Auth\Contracts\Services\Creator\User\UserCreator as UserCreatorContract;
use Auth\Entities\Exception\EmailAlreadyExists as DomainEmailAlreadyExists;
use Auth\UseCases\User\CreateUser\CreateUser;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(UserCreatorContract::class)]
final readonly class UserCreator implements UserCreatorContract
{
    public function __construct(
        private CreateUser $createUserUseCase,
    ) {
    }

    public function createUser(CreateUserCommand $command): CreatedUserResult
    {
        try {
            $response = $this->createUserUseCase->execute(
                new InternalCreateUserRequest($command)
            );

            return new CreatedUserResult(
                uuid: $response->user->uuid()->toString(),
                email: $response->user->email()->toString(),
            );
        } catch (DomainEmailAlreadyExists) {
            throw new EmailAlreadyExists($command->email);
        }
    }
}
