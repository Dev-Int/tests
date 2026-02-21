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

namespace Admin\Adapters\Gateway\Auth;

use Admin\Contracts\Services\PasswordResetTokenCreator;
use Admin\UseCases\Gateway\PasswordResetGateway;
use Shared\Entities\ResourceUuid;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(PasswordResetGateway::class)]
final readonly class ResetPasswordTokenGateway implements PasswordResetGateway
{
    public function __construct(private PasswordResetTokenCreator $resetTokenHandler)
    {
    }

    public function createResetToken(ResourceUuid $userId): string
    {
        return $this->resetTokenHandler->createResetToken($userId);
    }
}
