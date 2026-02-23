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

namespace Auth\UseCases\PasswordReset\CreatePasswordResetToken;

use Auth\Entities\Repository\PasswordResetTokenRepository;
use Auth\Entities\Repository\UserRepository;
use Auth\Entities\ResetPassword;
use Auth\UseCases\Gateway\TransactionGateway;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\ResourceUuid;

final readonly class CreatePasswordResetToken
{
    private const int TOKEN_VALIDITY_HOURS = 24;

    public function __construct(
        private PasswordResetTokenRepository $repository,
        private UserRepository $userRepository,
        private TransactionGateway $transactionGateway,
    ) {
    }

    public function execute(CreatePasswordResetTokenRequest $request): CreatePasswordResetTokenResponse
    {
        $token = bin2hex(random_bytes(32));

        $now = ClockFactory::clock()->now();
        $expiresAt = $now->modify(\sprintf('+%d hours', self::TOKEN_VALIDITY_HOURS));
        $userUuid = $request->userUuid();
        $user = $this->userRepository->getByUuid($userUuid);

        $resetPassword = new ResetPassword(
            id: ResourceUuid::generate(),
            user: $user,
            token: $token,
            expiresAt: $expiresAt,
            usedAt: null,
        );

        $this->transactionGateway->wrapInTransaction(
            $this->processPasswordReset($userUuid, $resetPassword)
        );

        return new CreatePasswordResetTokenResponse($token);
    }

    private function processPasswordReset(
        ResourceUuid $userUuid,
        ResetPassword $resetPassword
    ): \Closure {
        return function () use ($userUuid, $resetPassword): void {
            $this->repository->deleteByUserUuid($userUuid);

            $this->repository->create($resetPassword);
        };
    }
}
