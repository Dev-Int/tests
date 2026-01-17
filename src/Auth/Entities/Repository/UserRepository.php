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

namespace Auth\Entities\Repository;

use Auth\Entities\Exception\UserNotFound;
use Auth\Entities\User;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\EmailField;

interface UserRepository
{
    /**
     * @throws UserNotFound
     */
    public function getByUuid(ResourceUuid $uuid): User;

    /**
     * @throws UserNotFound
     */
    public function getByEmail(EmailField $email): User;

    public function emailExists(EmailField $email): bool;

    public function create(User $user): void;

    public function update(User $user): void;

    public function delete(User $user): void;
}
