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

namespace Admin\UseCases\Employee\GetActiveEmployees;

final readonly class GetActiveEmployeesApiRequest implements GetActiveEmployeesRequest
{
    public function __construct(
        private int $page = 1,
        private int $itemsPerPage = 20,
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
