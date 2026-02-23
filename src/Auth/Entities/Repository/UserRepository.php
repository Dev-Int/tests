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

use Auth\Entities\Exception\UserNotFoundByEmail;
use Auth\Entities\Exception\UserNotFoundById;
use Auth\Entities\User;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\EmailField;

interface UserRepository
{
    /**
     * @throws UserNotFoundById
     */
    public function getByUuid(ResourceUuid $uuid): User;

    /**
     * @throws UserNotFoundByEmail
     */
    public function getByEmail(EmailField $email): User;

    /**
     * Returns true if an email is registered, regardless of the user's active/disabled status.
     * An email from a disabled user remains permanently blocked — email is non-recyclable (ADR-008).
     */
    public function emailExists(EmailField $email): bool;

    public function create(User $user): void;

    public function update(User $user): void;
}
