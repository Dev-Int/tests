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

namespace Auth\Contracts\DTO;

final readonly class CurrentUserData
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

    public function hasRole(string $role): bool
    {
        return \in_array($role, $this->roles, true);
    }
}
