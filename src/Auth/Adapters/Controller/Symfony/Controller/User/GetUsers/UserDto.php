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

namespace Auth\Adapters\Controller\Symfony\Controller\User\GetUsers;

use Auth\Entities\User;
use Shared\Entities\Role;

final readonly class UserDto
{
    public static function fromDomain(User $user): self
    {
        return new self(
            uuid: $user->uuid()->toString(),
            email: $user->email()->toString(),
            roles: array_map(
                static fn (Role $role): string => $role->value,
                $user->roles()
            ),
            isActive: $user->isActive(),
            createdAt: $user->createdAt(),
            disabledAt: $user->disabledAt(),
        );
    }

    /**
     * @param array<string> $roles
     */
    public function __construct(
        public string $uuid,
        public string $email,
        public array $roles,
        public bool $isActive,
        public \DateTimeImmutable $createdAt,
        public ?\DateTimeImmutable $disabledAt,
    ) {
    }
}
