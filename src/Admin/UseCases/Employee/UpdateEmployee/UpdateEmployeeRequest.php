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

namespace Admin\UseCases\Employee\UpdateEmployee;

use Admin\Entities\Employee\ContactInformation;
use Admin\Entities\VO\EmployeeStatus;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\NameField;

interface UpdateEmployeeRequest
{
    public function uuid(): ResourceUuid;

    public function contactInformation(): ContactInformation;

    public function position(): NameField;

    public function department(): NameField;

    public function status(): EmployeeStatus;
}
