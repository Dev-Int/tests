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

use Admin\UseCases\DTO\CreatedUserDTO;
use Admin\UseCases\DTO\CreateUserDTO;
use Admin\UseCases\Employee\Exception\UserEmailAlreadyExists;
use Admin\UseCases\Gateway\UserCreatorGateway;
use Auth\Contracts\Exception\EmailAlreadyExists;
use Auth\Contracts\Services\Creator\User\CreateUserCommand;
use Auth\Contracts\Services\Creator\User\UserCreator;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\EmailField;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(UserCreatorGateway::class)]
final readonly class UserCreatorAdapter implements UserCreatorGateway
{
    public function __construct(
        private UserCreator $userCreator,
    ) {
    }

    /**
     * @throws UserEmailAlreadyExists
     */
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
        } catch (EmailAlreadyExists $exception) {
            throw new UserEmailAlreadyExists($dto->email);
        }
    }
}
