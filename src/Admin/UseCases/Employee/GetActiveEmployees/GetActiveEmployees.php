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

use Admin\Entities\Repository\EmployeeRepository;

final readonly class GetActiveEmployees
{
    public function __construct(
        private EmployeeRepository $repository,
    ) {
    }

    public function execute(GetActiveEmployeesRequest $request): GetActiveEmployeesResponse
    {
        $employees = $this->repository->getActiveEmployeesPaginated(
            $request->page(),
            $request->itemsPerPage()
        );

        return new GetActiveEmployeesResponse($employees);
    }
}
