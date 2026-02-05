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

namespace Auth\UseCases\User\UpdateUser;

use Shared\Entities\ResourceUuid;
use Shared\Entities\Role;
use Shared\Entities\VO\EmailField;

interface UpdateUserRequest
{
    public function uuid(): ResourceUuid;

    public function email(): ?EmailField;

    public function plainPassword(): ?string;

    /**
     * @return array<Role>|null
     */
    public function roles(): ?array;
}
