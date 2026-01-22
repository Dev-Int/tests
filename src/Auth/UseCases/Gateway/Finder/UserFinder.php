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

namespace Auth\UseCases\Gateway\Finder;

use Auth\Entities\User;
use Shared\Entities\ResourceUuid;

interface UserFinder
{
    public function findByUuid(ResourceUuid|string $uuid): ?User;

    /**
     * @return iterable<User>
     */
    public function findAllUsers(): iterable;

    /**
     * @return iterable<User>
     */
    public function findAllUsersPaginated(int $page, int $itemsPerPage): iterable;

    public function countAll(): int;

    /**
     * @return iterable<User>
     */
    public function findActiveUsers(): iterable;
}
