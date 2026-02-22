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

namespace Auth\Adapters\ContractsHandler\Provider\PasswordResetTokenCreator;

use Auth\UseCases\PasswordReset\CreatePasswordResetToken\CreatePasswordResetTokenRequest;
use Shared\Entities\ResourceUuid;

final readonly class CreatePasswordResetTokenProviderRequest implements CreatePasswordResetTokenRequest
{
    public function __construct(private ResourceUuid $userUuid)
    {
    }

    public function userUuid(): ResourceUuid
    {
        return $this->userUuid;
    }
}
