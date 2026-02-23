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

namespace Auth\Adapters\Controller\Symfony\Controller\User\GetUsers;

use Auth\UseCases\User\GetUsers\GetUsersResponse;

final class GetUsersWebResponse
{
    /** @var array<UserDto> */
    private array $users = [];
    private int $totalItems;

    public function __construct(GetUsersResponse $response)
    {
        foreach ($response->users as $user) {
            $this->users[] = UserDto::fromDomain($user);
        }
        $this->totalItems = $response->users->count();
    }

    /**
     * @return array<UserDto>
     */
    public function users(): array
    {
        return $this->users;
    }

    public function totalItems(): int
    {
        return $this->totalItems;
    }
}
