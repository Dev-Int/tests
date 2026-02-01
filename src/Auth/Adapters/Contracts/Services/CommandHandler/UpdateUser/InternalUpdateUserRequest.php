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

use Auth\Contracts\Services\CommandHandler\UpdateUser\UpdateUserCommand;
use Auth\UseCases\User\UpdateUser\UpdateUserRequest;
use Shared\Entities\ResourceUuid;
use Shared\Entities\Role;
use Shared\Entities\VO\EmailField;

final readonly class InternalUpdateUserRequest implements UpdateUserRequest
{
    private ResourceUuid $uuid;
    private ?EmailField $email;

    /** @var array<Role>|null */
    private ?array $roles;

    public function __construct(private UpdateUserCommand $command)
    {
        $this->uuid = ResourceUuid::fromString($this->command->uuid);
        $this->email = $this->command->email;
        $this->roles = $command->roles !== null
            ? array_map(
                static fn (string $role): Role => Role::from($role),
                $command->roles,
            )
            : null;
    }

    public function uuid(): ResourceUuid
    {
        return $this->uuid;
    }

    public function email(): ?EmailField
    {
        return $this->email;
    }

    public function plainPassword(): ?string
    {
        return $this->command->plainPassword;
    }

    /**
     * @return array<Role>|null
     */
    public function roles(): ?array
    {
        return $this->roles;
    }
}
