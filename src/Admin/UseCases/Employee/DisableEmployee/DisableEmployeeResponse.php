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

namespace Admin\UseCases\Employee\DisableEmployee;

use Admin\Entities\Employee\Employee;

final readonly class DisableEmployeeResponse
{
    public function __construct(private Employee $employee)
    {
    }

    public function employee(): Employee
    {
        return $this->employee;
    }
}
