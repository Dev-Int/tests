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

namespace Admin\Adapters\Gateway\Auth;

use Admin\Entities\Exception\Employee\EmployeeAlreadyExists;
use Admin\UseCases\DTO\CreatedUserDTO;
use Admin\UseCases\DTO\CreateUserDTO;
use Admin\UseCases\Gateway\UserCreatorGateway;
use Auth\Contracts\Exception\EmailAlreadyExists;
use Auth\Contracts\Services\CommandHandler\CreateUser\CreateUserCommand;
use Auth\Contracts\Services\CommandHandler\CreateUser\CreateUserCommandHandler;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\EmailField;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(UserCreatorGateway::class)]
final readonly class UserCreatorAdapter implements UserCreatorGateway
{
    public function __construct(
        private CreateUserCommandHandler $userCreator,
    ) {
    }

    public function createUser(CreateUserDTO $dto): CreatedUserDTO
    {
        try {
            $result = $this->userCreator->createUser(
                new CreateUserCommand(
                    email: $dto->email,
                    plainPassword: $dto->plainPassword,
                    roles: $dto->roles,
                )
            );

            return new CreatedUserDTO(
                uuid: ResourceUuid::fromString($result->uuid),
                email: EmailField::fromString($result->email),
            );
        } catch (EmailAlreadyExists) {
            throw new EmployeeAlreadyExists($dto->email);
        }
    }
}
