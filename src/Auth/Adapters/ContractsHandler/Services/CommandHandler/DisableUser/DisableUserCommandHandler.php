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

namespace Auth\Adapters\ContractsHandler\Services\CommandHandler\DisableUser;

use Auth\Contracts\Exception\UserAlreadyDisabled;
use Auth\Contracts\Exception\UserNotFound;
use Auth\Contracts\Services\CommandHandler\DisableUser\DisabledUserResult;
use Auth\Contracts\Services\CommandHandler\DisableUser\DisableUserCommand;
use Auth\Contracts\Services\CommandHandler\DisableUser\DisableUserCommandHandler as UserDisablerContract;
use Auth\Entities\Exception\UserAlreadyDisabled as DomainUserAlreadyDisabled;
use Auth\Entities\Exception\UserNotFoundById;
use Auth\UseCases\User\DisableUser\DisableUser;
use Shared\Entities\ResourceUuid;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(UserDisablerContract::class)]
final readonly class DisableUserCommandHandler implements UserDisablerContract
{
    public function __construct(
        private DisableUser $disableUserUseCase,
    ) {
    }

    public function disableUser(DisableUserCommand $command): DisabledUserResult
    {
        try {
            $response = $this->disableUserUseCase->execute(
                new InternalDisableUserRequest(ResourceUuid::fromString($command->uuid))
            );

            return new DisabledUserResult(
                uuid: $response->user->uuid()->toString(),
                email: $response->user->email()->toString(),
                isActive: $response->user->isActive(),
            );
        } catch (DomainUserAlreadyDisabled) {
            throw new UserAlreadyDisabled($command->uuid);
        } catch (UserNotFoundById) {
            throw new UserNotFound($command->uuid);
        }
    }
}
