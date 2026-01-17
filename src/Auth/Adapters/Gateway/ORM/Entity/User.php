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

namespace Auth\Adapters\Gateway\ORM\Entity;

use Auth\Adapters\Gateway\ORM\Repository\DoctrineUserRepository;
use Auth\Entities\Role;
use Auth\Entities\User as UserDomain;
use Auth\Entities\VO\HashedPassword;
use Doctrine\ORM\Mapping as ORM;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\EmailField;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: DoctrineUserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\Index(name: 'idx_user_disabled_at', columns: ['disabled_at'])]
#[ORM\UniqueConstraint(name: 'uniq_user_email', columns: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /** @var array<string> */
    #[ORM\Column(type: 'json')]
    private array $roles;

    #[ORM\Column(type: 'datetimetz_immutable')]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(type: 'datetimetz_immutable', nullable: true)]
    private ?\DateTimeImmutable $disabledAt = null;

    public static function fromDomain(UserDomain $user): self
    {
        return new self(
            uuid: $user->uuid()->toString(),
            email: $user->email()->toString(),
            password: $user->password()->toString(),
            roles: array_map(
                static fn (Role $role): string => $role->value,
                $user->roles(),
            ),
            createdAt: $user->createdAt(),
            updatedAt: $user->updatedAt(),
            disabledAt: $user->disabledAt(),
        );
    }

    /**
     * @param array<string> $roles
     */
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'guid')]
        private readonly string $uuid,
        #[ORM\Column(type: 'string', length: 180)]
        private string $email,
        #[ORM\Column(type: 'string', length: 255)]
        private string $password,
        array $roles,
        #[ORM\Column(type: 'datetimetz_immutable')]
        private readonly \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
        ?\DateTimeImmutable $disabledAt = null,
    ) {
        $this->roles = $roles;
        $this->updatedAt = $updatedAt;
        $this->disabledAt = $disabledAt;
    }

    public function toDomain(): UserDomain
    {
        return UserDomain::reconstitute(
            uuid: ResourceUuid::fromString($this->uuid),
            email: EmailField::fromString($this->email),
            password: HashedPassword::fromHash($this->password),
            roles: array_map(
                static fn (string $role): Role => Role::from($role),
                $this->roles,
            ),
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
            disabledAt: $this->disabledAt,
        );
    }

    public function updateFromDomain(UserDomain $user): void
    {
        $this->email = $user->email()->toString();
        $this->password = $user->password()->toString();
        $this->roles = array_map(
            static fn (Role $role): string => $role->value,
            $user->roles(),
        );
        $this->updatedAt = $user->updatedAt();
        $this->disabledAt = $user->disabledAt();
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    /**
     * @return non-empty-string
     */
    public function getUserIdentifier(): string
    {
        \assert($this->email !== '');

        return $this->email;
    }

    /**
     * @return array<string>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function eraseCredentials(): void
    {
        // We don't store plaintext password, nothing to erase
    }

    public function disabledAt(): ?\DateTimeImmutable
    {
        return $this->disabledAt;
    }

    public function isActive(): bool
    {
        return !$this->disabledAt instanceof \DateTimeImmutable;
    }
}
