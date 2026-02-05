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

namespace Auth\Adapters\Contracts\Provider;

use Admin\Contracts\Services\PasswordResetTokenCreator;
use Auth\Adapters\Gateway\Provider\PasswordResetProvider;
use Shared\Entities\ResourceUuid;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(PasswordResetTokenCreator::class)]
final readonly class PasswordResetTokenCreatorProvider implements PasswordResetTokenCreator
{
    public function __construct(
        private PasswordResetProvider $passwordResetProvider,
    ) {
    }

    public function createResetToken(ResourceUuid $userUuid): string
    {
        return $this->passwordResetProvider->createResetToken($userUuid);
    }
}
