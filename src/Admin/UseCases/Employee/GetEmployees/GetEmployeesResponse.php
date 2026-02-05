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

use Admin\Entities\Employee\Employee;
use Admin\Entities\Employee\EmployeeCollection;

final readonly class GetEmployeesResponse
{
    public function __construct(
        private EmployeeCollection $employees,
    ) {
    }

    /**
     * @return array<Employee>
     */
    public function employees(): array
    {
        return $this->employees->toArray();
    }
}
