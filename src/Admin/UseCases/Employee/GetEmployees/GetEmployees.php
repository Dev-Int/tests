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

namespace Admin\UseCases\Employee\GetEmployees;

use Admin\UseCases\Gateway\Finder\EmployeeFinder;

/**
 * Returns all employees regardless of status (active and inactive), without pagination.
 *
 * Intended for administrative exports or internal integrations requiring complete data.
 * For the Admin UI listing (active only, paginated), use GetActiveEmployees.
 */
final readonly class GetEmployees
{
    public function __construct(
        private EmployeeFinder $employeeFinder,
    ) {
    }

    public function execute(): GetEmployeesResponse
    {
        $employees = $this->employeeFinder->getAllEmployees();

        return new GetEmployeesResponse($employees);
    }
}
