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

use Shared\Entities\ResourceUuid;

final class ResetPassword
{
    public function __construct(
        public readonly ResourceUuid $id,
        public readonly ResourceUuid $userId,
        public readonly string $token,
        public readonly bool $isExpired,
        private bool $isUsed,
    ) {
    }

    public function isUsed(): bool
    {
        return $this->isUsed;
    }

    public function used(): void
    {
        $this->isUsed = true;
    }
}
