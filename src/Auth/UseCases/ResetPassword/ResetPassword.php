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

namespace Auth\UseCases\ResetPassword;

use Auth\Entities\Exception\InvalidPasswordResetToken;
use Auth\Entities\Repository\PasswordResetTokenRepository;
use Auth\Entities\Repository\UserRepository;
use Auth\UseCases\Gateway\PasswordHasherGateway;

final readonly class ResetPassword
{
    public function __construct(
        private UserRepository $userRepository,
        private PasswordResetTokenRepository $resetTokenRepository,
        private PasswordHasherGateway $passwordHasher,
    ) {
    }

    public function execute(ResetPasswordRequest $request): void
    {
        $passwordResetToken = $request->token();

        if (!$passwordResetToken->canBeUsed()) {
            throw new InvalidPasswordResetToken();
        }

        $user = $passwordResetToken->user();

        $hashedPassword = $this->passwordHasher->hashPassword($request->plainPassword());
        $user->changePassword($hashedPassword);
        $this->userRepository->update($user);

        $passwordResetToken->used();
        $this->resetTokenRepository->save($passwordResetToken);
    }
}
