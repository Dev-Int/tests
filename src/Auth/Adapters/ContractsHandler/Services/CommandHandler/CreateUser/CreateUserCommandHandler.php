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

namespace Auth\Adapters\ContractsHandler\Services\CommandHandler\CreateUser;

use Auth\Contracts\Exception\EmailAlreadyExists;
use Auth\Contracts\Services\CommandHandler\CreateUser\CreatedUserResult;
use Auth\Contracts\Services\CommandHandler\CreateUser\CreateUserCommand;
use Auth\Contracts\Services\CommandHandler\CreateUser\CreateUserCommandHandler as UserCreatorContract;
use Auth\Entities\Exception\EmailAlreadyExists as DomainEmailAlreadyExists;
use Auth\UseCases\User\CreateUser\CreateUser;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(UserCreatorContract::class)]
final readonly class CreateUserCommandHandler implements UserCreatorContract
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
