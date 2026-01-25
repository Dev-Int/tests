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

use Auth\UseCases\Gateway\Finder\UserFinder;

final readonly class GetUsers
{
    public function __construct(private UserFinder $userFinder)
    {
    }

    public function execute(GetUsersRequest $request): GetUsersResponse
    {
        $collection = $this->userFinder->findAllUsersPaginated(
            $request->page(),
            $request->itemsPerPage()
        );

        return new GetUsersResponse($collection);
    }
}
