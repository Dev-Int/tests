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

use Admin\Entities\Exception\Employee\EmployeeAlreadyDisabled;
use Admin\Entities\Exception\Employee\EmployeeNotFound;
use Admin\UseCases\Gateway\UserDisablerGateway;
use Auth\Contracts\Exception\UserAlreadyDisabled as AuthUserAlreadyDisabled;
use Auth\Contracts\Exception\UserNotFound as AuthUserNotFound;
use Auth\Contracts\Services\CommandHandler\DisableUser\DisableUserCommand;
use Auth\Contracts\Services\CommandHandler\DisableUser\DisableUserCommandHandler;
use Shared\Entities\ResourceUuid;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(UserDisablerGateway::class)]
final readonly class UserDisablerAdapter implements UserDisablerGateway
{
    public function __construct(
        private DisableUserCommandHandler $userDisabler,
    ) {
    }

    public function disableUser(ResourceUuid $userUuid): void
    {
        try {
            $this->userDisabler->disableUser(
                new DisableUserCommand(
                    uuid: $userUuid->toString(),
                )
            );
        } catch (AuthUserAlreadyDisabled) {
            throw new EmployeeAlreadyDisabled(linkedUserUuid: $userUuid);
        } catch (AuthUserNotFound) {
            throw new EmployeeNotFound($userUuid);
        }
    }
}
