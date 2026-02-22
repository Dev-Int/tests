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

namespace Auth\Contracts\Services\CommandHandler\UpdateUser;

final readonly class UpdatedUserResult
{
    /**
     * @param array<string> $roles
     */
    public function __construct(
        public string $uuid,
        public string $email,
        public array $roles,
    ) {
    }
}
