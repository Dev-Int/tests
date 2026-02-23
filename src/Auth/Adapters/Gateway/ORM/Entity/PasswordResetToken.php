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

use Auth\Entities\ResetPassword;
use Doctrine\ORM\Mapping as ORM;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\ResourceUuid;

#[ORM\Entity]
#[ORM\Table(name: 'password_reset_tokens')]
#[ORM\Index(name: 'idx_reset_expires_at', columns: ['expires_at'])]
class PasswordResetToken
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'guid')]
        private readonly string $uuid,
        #[ORM\ManyToOne(targetEntity: User::class)]
        #[ORM\JoinColumn(name: 'user_uuid', referencedColumnName: 'uuid', nullable: false)]
        private readonly User $user,
        #[ORM\Column(type: 'string', length: 64, unique: true)]
        private readonly string $token,
        #[ORM\Column(type: 'datetimetz_immutable')]
        private readonly \DateTimeImmutable $createdAt,
        #[ORM\Column(type: 'datetimetz_immutable')]
        private readonly \DateTimeImmutable $expiresAt,
        #[ORM\Column(type: 'datetimetz_immutable', nullable: true)]
        private ?\DateTimeImmutable $usedAt = null,
    ) {
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function user(): User
    {
        return $this->user;
    }

    public function token(): string
    {
        return $this->token;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function expiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt < ClockFactory::clock()->now();
    }

    public function isUsed(): bool
    {
        return $this->usedAt instanceof \DateTimeImmutable;
    }

    public function updateFromDomain(ResetPassword $resetPassword): void
    {
        if (
            $resetPassword->usedAt() instanceof \DateTimeImmutable
            && !$this->usedAt instanceof \DateTimeImmutable
        ) {
            $this->usedAt = $resetPassword->usedAt();
        }
    }

    public function toDomain(): ResetPassword
    {
        return new ResetPassword(
            ResourceUuid::fromString($this->uuid),
            $this->user->toDomain(),
            $this->token,
            $this->expiresAt,
            $this->usedAt,
        );
    }
}
