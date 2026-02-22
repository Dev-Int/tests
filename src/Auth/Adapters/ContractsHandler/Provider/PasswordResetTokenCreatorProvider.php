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

namespace Auth\Adapters\ContractsHandler\Provider;

use Auth\Adapters\ContractsHandler\Provider\PasswordResetTokenCreator\CreatePasswordResetTokenProviderRequest;
use Auth\Contracts\Services\CommandHandler\PasswordResetToken\PasswordResetTokenCreator;
use Auth\UseCases\PasswordReset\CreatePasswordResetToken\CreatePasswordResetToken;
use Shared\Entities\ResourceUuid;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(PasswordResetTokenCreator::class)]
final readonly class PasswordResetTokenCreatorProvider implements PasswordResetTokenCreator
{
    public function __construct(private CreatePasswordResetToken $useCase)
    {
    }

    public function createResetToken(ResourceUuid $userUuid): string
    {
        $response = $this->useCase->execute(new CreatePasswordResetTokenProviderRequest($userUuid));

        return $response->token;
    }
}
