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

namespace Auth\Adapters\Security;

use Auth\Adapters\Gateway\ORM\Entity\User as UserOrm;
use Auth\Entities\Exception\UserNotFoundByEmail;
use Auth\Entities\Exception\UserNotFoundById;
use Auth\Entities\Repository\UserRepository;
use Shared\Entities\Exception\InvalidEmailException;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\EmailField;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Symfony Security User Provider for the Auth BC.
 *
 * Architecture note: This provider creates detached ORM instances via UserOrm::fromDomain().
 * This is intentional as Symfony Security only reads user data (no modifications).
 *
 * If you implement PasswordUpgraderInterface or Remember Me, you'll need managed entities.
 * In that case, switch to Option C: add findOrmByEmail()/findOrmByUuid() methods
 * to DoctrineUserRepository that return managed UserOrm instances directly.
 *
 * @see https://symfony.com/doc/current/security/user_providers.html
 *
 * @implements UserProviderInterface<UserOrm>
 */
final readonly class SymfonyUserProvider implements UserProviderInterface
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        try {
            $domainUser = $this->userRepository->getByEmail(
                EmailField::fromString($identifier)
            );

            if (!$domainUser->isActive()) {
                throw new UserNotFoundException(\sprintf('User "%s" is disabled.', $identifier));
            }

            return UserOrm::fromDomain($domainUser);
        } catch (InvalidEmailException | UserNotFoundByEmail) {
            throw new UserNotFoundException(\sprintf('User "%s" not found.', $identifier));
        }
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof UserOrm) {
            throw new UnsupportedUserException(\sprintf('Instances of "%s" are not supported.', $user::class));
        }

        try {
            $domainUser = $this->userRepository->getByUuid(
                ResourceUuid::fromString($user->uuid())
            );

            if (!$domainUser->isActive()) {
                throw new UserNotFoundException(\sprintf('User "%s" is disabled.', $user->getUserIdentifier()));
            }

            return UserOrm::fromDomain($domainUser);
        } catch (UserNotFoundById) {
            throw new UserNotFoundException(\sprintf('User "%s" not found.', $user->getUserIdentifier()));
        }
    }

    public function supportsClass(string $class): bool
    {
        return $class === UserOrm::class;
    }
}
