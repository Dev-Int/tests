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

use Admin\UseCases\Gateway\Finder\EmployeeFinder;

/**
 * Returns active employees only (disabledAt IS NULL), with pagination.
 *
 * Used by the Admin UI listing. For full data access (all statuses), use GetEmployees.
 */
final readonly class GetActiveEmployees
{
    public function __construct(
        private EmployeeFinder $employeeFinder,
    ) {
    }

    public function execute(GetActiveEmployeesRequest $request): GetActiveEmployeesResponse
    {
        $employees = $this->employeeFinder->getActiveEmployeesPaginated(
            $request->page(),
            $request->itemsPerPage()
        );
        $totalCount = $this->employeeFinder->getActiveEmployeesCount();

        return new GetActiveEmployeesResponse($totalCount, $employees);
    }
}
