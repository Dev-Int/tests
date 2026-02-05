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

namespace Auth\Adapters\Gateway\Provider;

use Auth\Adapters\Gateway\ORM\Entity\PasswordResetToken;
use Auth\Adapters\Gateway\ORM\Entity\User;
use Auth\Adapters\Gateway\ORM\Repository\DoctrinePasswordResetTokenRepository;
use Auth\Adapters\Gateway\ORM\Repository\DoctrineUserRepository;
use Auth\Entities\Exception\UserNotFoundById;
use Shared\Entities\ResourceUuid;

final readonly class PasswordResetProvider
{
    private const int TOKEN_VALIDITY_HOURS = 24;

    public function __construct(
        private DoctrinePasswordResetTokenRepository $repository,
        private DoctrineUserRepository $userRepository,
    ) {
    }

    public function createResetToken(ResourceUuid $userUuid): string
    {
        // Générer un token sécurisé (64 caractères hexadécimaux)
        $token = bin2hex(random_bytes(32));

        $now = new \DateTimeImmutable();
        $expiresAt = $now->modify(\sprintf('+%d hours', self::TOKEN_VALIDITY_HOURS));

        $userOrm = $this->userRepository->find($userUuid->toString());
        if (!$userOrm instanceof User) {
            throw new UserNotFoundById($userUuid);
        }

        $resetToken = new PasswordResetToken(
            uuid: ResourceUuid::generate()->toString(),
            user: $userOrm,
            token: $token,
            createdAt: $now,
            expiresAt: $expiresAt,
        );

        $this->repository->create($resetToken);

        return $token;
    }
}
