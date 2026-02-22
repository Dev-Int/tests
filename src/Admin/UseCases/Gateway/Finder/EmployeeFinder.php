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

namespace Admin\UseCases\Gateway\Finder;

use Admin\Entities\Employee\EmployeeCollection;
use Admin\Entities\Exception\Employee\NoEmployeeRegistered;
use Admin\Entities\Exception\Employee\PageOutOfRange;

interface EmployeeFinder
{
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
     * @throws PageOutOfRange
     */
    public function getActiveEmployeesPaginated(int $page, int $itemsPerPage): EmployeeCollection;

    public function getActiveEmployeesCount(): int;
}
