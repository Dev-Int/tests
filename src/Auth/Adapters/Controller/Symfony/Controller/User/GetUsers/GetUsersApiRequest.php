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

use Auth\UseCases\User\GetUsers\GetUsersRequest;

final readonly class GetUsersApiRequest implements GetUsersRequest
{
    public function __construct(
        public int $page,
        public int $itemsPerPage,
    ) {
    }

    public function page(): int
    {
        return $this->page;
    }

    public function itemsPerPage(): int
    {
        return $this->itemsPerPage;
    }
}
