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

namespace Admin\Entities\Repository;

use Admin\Entities\Employee\Employee;
use Admin\Entities\Employee\EmployeeCollection;
use Admin\Entities\Exception\Employee\EmployeeNotFound;
use Admin\Entities\Exception\Employee\NoEmployeeRegistered;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\EmailField;

interface EmployeeRepository
{
    /**
     * @throws EmployeeNotFound
     */
    public function getByUuid(ResourceUuid $uuid): Employee;

    /**
     * @throws EmployeeNotFound
     */
    public function getByEmail(EmailField $email): Employee;

    /**
     * @throws NoEmployeeRegistered
     */
    public function getAllEmployees(): EmployeeCollection;

    /**
     * @throws NoEmployeeRegistered
     */
    public function getActiveEmployees(): EmployeeCollection;

    /**
     * @throws NoEmployeeRegistered
     */
    public function getActiveEmployeesPaginated(int $page, int $itemsPerPage): EmployeeCollection;

    public function getActiveEmployeesCount(): int;

    public function emailExists(EmailField $email): bool;

    public function hasEmployees(): bool;

    public function save(Employee $employee): void;

    public function update(Employee $employee): void;
}
