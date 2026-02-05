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

namespace Auth\Adapters\Gateway\Security;

use Auth\Entities\VO\HashedPassword;
use Auth\UseCases\Gateway\PasswordHasherGateway;
use Auth\UseCases\User\CreateUser\PasswordHasherUser;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsAlias(PasswordHasherGateway::class)]
final readonly class SymfonyPasswordHasher implements PasswordHasherGateway
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function hashPassword(string $plainPassword): HashedPassword
    {
        $hash = $this->passwordHasher->hashPassword(
            new PasswordHasherUser(),
            $plainPassword
        );

        return HashedPassword::fromHash($hash);
    }
}
