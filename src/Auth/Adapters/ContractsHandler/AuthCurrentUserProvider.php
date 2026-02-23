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

namespace Auth\Adapters\ContractsHandler;

use Auth\Adapters\Gateway\ORM\Entity\User;
use Auth\Contracts\CurrentUserProvider;
use Auth\Contracts\DTO\CurrentUserData;
use Auth\Contracts\Exception\UnauthenticatedUser;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[AsAlias(CurrentUserProvider::class)]
final readonly class AuthCurrentUserProvider implements CurrentUserProvider
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function getCurrentUser(): ?CurrentUserData
    {
        $token = $this->tokenStorage->getToken();
        if (!$token instanceof TokenInterface) {
            return null;
        }

        $user = $token->getUser();
        if (!$user instanceof User) {
            return null;
        }

        return new CurrentUserData(
            uuid: $user->uuid(),
            email: $user->getUserIdentifier(),
            roles: $user->getRoles(),
        );
    }

    public function getCurrentUserOrFail(): CurrentUserData
    {
        $currentUser = $this->getCurrentUser();

        if (!$currentUser instanceof CurrentUserData) {
            throw new UnauthenticatedUser();
        }

        return $currentUser;
    }

    public function isAuthenticated(): bool
    {
        return $this->getCurrentUser() instanceof CurrentUserData;
    }

    public function hasRole(string $role): bool
    {
        if (!$this->isAuthenticated()) {
            return false;
        }

        return $this->authorizationChecker->isGranted($role);
    }
}
