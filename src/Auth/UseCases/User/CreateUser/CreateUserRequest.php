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

namespace Auth\UseCases\User\CreateUser;

use Shared\Entities\Role;
use Shared\Entities\VO\EmailField;

interface CreateUserRequest
{
    public function email(): EmailField;

    public function plainPassword(): string;

    /**
     * @return array<Role>
     */
    public function roles(): array;
}
