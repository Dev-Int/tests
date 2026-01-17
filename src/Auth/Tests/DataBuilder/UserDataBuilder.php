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

namespace Auth\Tests\DataBuilder;

use Auth\Entities\Role;
use Auth\Entities\User;
use Auth\Entities\VO\HashedPassword;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\EmailField;

final class UserDataBuilder
{
    private ResourceUuid $uuid;
    private EmailField $email;
    private HashedPassword $password;

    /** @var array<Role> */
    private array $roles = [Role::USER];
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    public static function aUser(): self
    {
        return new self();
    }

    private function __construct()
    {
        $this->uuid = ResourceUuid::generate();
        $this->email = EmailField::fromString('user@example.com');
        $this->password = HashedPassword::fromHash('$2y$13$hashedpassword');
        $now = ClockFactory::clock()->now();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function withUuid(ResourceUuid $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function withEmail(string $email): self
    {
        $this->email = EmailField::fromString($email);

        return $this;
    }

    public function withPassword(string $hashedPassword): self
    {
        $this->password = HashedPassword::fromHash($hashedPassword);

        return $this;
    }

    /**
     * @param array<Role> $roles
     */
    public function withRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function asAdmin(): self
    {
        $this->roles = [Role::ADMIN, Role::USER];

        return $this;
    }

    public function asInventoryManager(): self
    {
        $this->roles = [Role::INVENTORY_MANAGER, Role::USER];

        return $this;
    }

    public function withCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function withUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function build(): User
    {
        return User::reconstitute(
            uuid: $this->uuid,
            email: $this->email,
            password: $this->password,
            roles: $this->roles,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
        );
    }
}
