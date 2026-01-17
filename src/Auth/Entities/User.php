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

namespace Auth\Entities;

use Auth\Entities\Exception\UserAlreadyDisabled;
use Auth\Entities\VO\HashedPassword;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\EmailField;

final class User
{
    private const Role DEFAULT_ROLE = Role::USER;

    /**
     * @param array<Role> $roles
     */
    public static function create(
        ResourceUuid $uuid,
        EmailField $email,
        HashedPassword $password,
        array $roles = [],
    ): self {
        $roles = self::normalizeRoles($roles);

        return new self(
            uuid: $uuid,
            email: $email,
            password: $password,
            roles: $roles,
            createdAt: ClockFactory::clock()->now(),
            updatedAt: ClockFactory::clock()->now(),
        );
    }

    /**
     * @param array<Role> $roles
     */
    public static function reconstitute(
        ResourceUuid $uuid,
        EmailField $email,
        HashedPassword $password,
        array $roles,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
        ?\DateTimeImmutable $disabledAt = null,
    ): self {
        return new self(
            uuid: $uuid,
            email: $email,
            password: $password,
            roles: self::normalizeRoles($roles),
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            disabledAt: $disabledAt,
        );
    }

    /**
     * @param array<Role> $roles
     */
    private function __construct(
        private readonly ResourceUuid $uuid,
        private EmailField $email,
        private HashedPassword $password,
        private array $roles,
        private readonly \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
        private ?\DateTimeImmutable $disabledAt = null,
    ) {
    }

    public function uuid(): ResourceUuid
    {
        return $this->uuid;
    }

    public function email(): EmailField
    {
        return $this->email;
    }

    public function password(): HashedPassword
    {
        return $this->password;
    }

    /**
     * @return array<Role>
     */
    public function roles(): array
    {
        return $this->roles;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function disabledAt(): ?\DateTimeImmutable
    {
        return $this->disabledAt;
    }

    public function isActive(): bool
    {
        return !$this->disabledAt instanceof \DateTimeImmutable;
    }

    public function disable(): void
    {
        if (!$this->isActive()) {
            throw new UserAlreadyDisabled($this->uuid);
        }
        $this->disabledAt = ClockFactory::clock()->now();
        $this->updatedAt = ClockFactory::clock()->now();
    }

    public function hasRole(Role $role): bool
    {
        return \in_array($role, $this->roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(Role::ADMIN);
    }

    public function changeEmail(EmailField $newEmail): void
    {
        $this->email = $newEmail;
        $this->updatedAt = ClockFactory::clock()->now();
    }

    public function changePassword(HashedPassword $newPassword): void
    {
        $this->password = $newPassword;
        $this->updatedAt = ClockFactory::clock()->now();
    }

    /**
     * @param array<Role> $roles
     */
    public function updateRoles(array $roles): void
    {
        $this->roles = self::normalizeRoles($roles);
        $this->updatedAt = ClockFactory::clock()->now();
    }

    /**
     * Ensures ROLE_USER is always present and roles are unique.
     *
     * @param array<Role> $roles
     *
     * @return array<Role>
     */
    private static function normalizeRoles(array $roles): array
    {
        $roles[] = self::DEFAULT_ROLE;

        // Use enum value as a key to ensure uniqueness
        $unique = [];
        foreach ($roles as $role) {
            $unique[$role->value] = $role;
        }

        return array_values($unique);
    }
}
