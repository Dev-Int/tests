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

use Admin\Entities\Repository\EmployeeRepository;

final readonly class GetEmployees
{
    public function __construct(
        private EmployeeRepository $repository,
    ) {
    }

    public function execute(): GetEmployeesResponse
    {
        $employees = $this->repository->getAllEmployees();

        return new GetEmployeesResponse($employees);
    }
}
