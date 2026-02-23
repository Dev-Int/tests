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

namespace Auth\Contracts;

use Auth\Contracts\DTO\CurrentUserData;
use Auth\Contracts\Exception\UnauthenticatedUser;

interface CurrentUserProvider
{
    public function getCurrentUser(): ?CurrentUserData;

    /**
     * @throws UnauthenticatedUser when no authenticated user is found
     */
    public function getCurrentUserOrFail(): CurrentUserData;

    public function isAuthenticated(): bool;

    public function hasRole(string $role): bool;
}
