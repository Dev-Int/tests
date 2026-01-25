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

namespace Auth\UseCases\User\CreateUser;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * Simple user implementation for password hashing.
 * This is needed because Symfony's PasswordHasherInterface requires a UserInterface.
 *
 * @internal
 */
final class PasswordHasherUser implements PasswordAuthenticatedUserInterface
{
    public function getPassword(): ?string
    {
        return null;
    }
}
