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

namespace Auth\Adapters\Gateway\ORM\Repository;

use Auth\Entities\Repository\UserRepository;
use Auth\Entities\User;
use Auth\Tests\DataBuilder\UserDataBuilder;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\EmailField;

/**
 * Stub implementation - ORM integration in issue #228.
 *
 * @see https://github.com/Dev-Int/tests/issues/228
 */
final class DoctrineUserRepository implements UserRepository
{
    public function getByUuid(ResourceUuid $uuid): User
    {
        // Stub: return fake user
        return UserDataBuilder::aUser()->build();
    }

    public function getByEmail(EmailField $email): User
    {
        // Stub: return fake user
        return UserDataBuilder::aUser()->build();
    }

    public function emailExists(EmailField $email): bool
    {
        // Stub: minimal implementation
        return true;
    }

    public function create(User $user): void
    {
        // To implement with Doctrine...
    }

    public function update(User $user): void
    {
        // To implement with Doctrine...
    }

    public function delete(User $user): void
    {
        // To implement with Doctrine...
    }
}
