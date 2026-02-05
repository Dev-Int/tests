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

use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\ResourceUuid;

final class ResetPassword
{
    public function __construct(
        public readonly ResourceUuid $id,
        private readonly User $user,
        public readonly string $token,
        public readonly bool $isExpired,
        private ?\DateTimeImmutable $usedAt,
    ) {
    }

    public function user(): User
    {
        return $this->user;
    }

    public function usedAt(): ?\DateTimeImmutable
    {
        return $this->usedAt;
    }

    public function isUsed(): bool
    {
        return $this->usedAt instanceof \DateTimeImmutable;
    }

    public function canBeUsed(): bool
    {
        return !$this->isExpired
            && !$this->isUsed()
            && $this->user->isActive();
    }

    public function used(): void
    {
        $this->usedAt = ClockFactory::clock()->now();
    }
}
