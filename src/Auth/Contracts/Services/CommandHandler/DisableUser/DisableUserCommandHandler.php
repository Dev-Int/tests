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

namespace Auth\Contracts\Services\CommandHandler\DisableUser;

use Auth\Contracts\Exception\UserAlreadyDisabled;
use Auth\Contracts\Exception\UserNotFound;

interface DisableUserCommandHandler
{
    /**
     * @throws UserAlreadyDisabled
     * @throws UserNotFound
     */
    public function disableUser(DisableUserCommand $command): DisabledUserResult;
}
