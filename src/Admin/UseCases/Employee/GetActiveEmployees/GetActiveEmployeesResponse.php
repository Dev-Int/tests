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

use Admin\Entities\Employee\Employee;
use Admin\Entities\Employee\EmployeeCollection;

final readonly class GetActiveEmployeesResponse
{
    public function __construct(
        private int $totalCount,
        private EmployeeCollection $employees,
    ) {
    }

    public function totalCount(): int
    {
        return $this->totalCount;
    }

    /**
     * @return array<Employee>
     */
    public function employees(): array
    {
        return $this->employees->toArray();
    }
}
