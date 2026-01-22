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

namespace Auth\UseCases\User\GetUsers;

use Auth\Entities\UserCollection;
use Auth\UseCases\Gateway\Finder\UserFinder;

final readonly class GetUsers
{
    public function __construct(private UserFinder $userFinder)
    {
    }

    public function execute(GetUsersRequest $request): GetUsersResponse
    {
        $users = $this->userFinder->findAllUsersPaginated($request->page(), $request->itemsPerPage());
        $totalItems = $this->userFinder->countAll();

        $collection = new UserCollection($totalItems);
        foreach ($users as $user) {
            $collection->add($user);
        }

        return new GetUsersResponse($collection);
    }
}
