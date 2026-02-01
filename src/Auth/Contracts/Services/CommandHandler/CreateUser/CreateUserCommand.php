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

namespace Auth\Contracts\Services\CommandHandler\CreateUser;

use Shared\Entities\Role;
use Shared\Entities\VO\EmailField;

final readonly class CreateUserCommand
{
    /**
     * @param array<Role> $roles
     */
    public function __construct(
        public EmailField $email,
        public string $plainPassword,
        public array $roles = [],
    ) {
    }
}
