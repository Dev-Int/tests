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

namespace Auth\Adapters\Contracts\Services\CommandHandler\DisableUser;

use Auth\UseCases\User\DisableUser\DisableUserRequest;
use Shared\Entities\ResourceUuid;

final readonly class InternalDisableUserRequest implements DisableUserRequest
{
    public function __construct(private ResourceUuid $uuid)
    {
    }

    public function uuid(): ResourceUuid
    {
        return $this->uuid;
    }
}
