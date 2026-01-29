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

namespace Auth\Adapters\Contracts\Services\CommandHandler\CreateUser;

use Auth\Contracts\Services\CommandHandler\CreateUser\CreateUserCommand;
use Auth\Entities\Role;
use Auth\UseCases\User\CreateUser\CreateUserRequest;
use Shared\Entities\VO\EmailField;

final readonly class InternalCreateUserRequest implements CreateUserRequest
{
    private EmailField $email;
    private string $plainPassword;

    /** @var array<Role> */
    private array $roles;

    public function __construct(CreateUserCommand $command)
    {
        $this->email = $command->email;
        $this->plainPassword = $command->plainPassword;
        $this->roles = array_map(
            static fn (string $role): Role => Role::from($role),
            $command->roles,
        );
    }

    public function email(): EmailField
    {
        return $this->email;
    }

    public function plainPassword(): string
    {
        return $this->plainPassword;
    }

    /**
     * @return array<Role>
     */
    public function roles(): array
    {
        return $this->roles;
    }
}
