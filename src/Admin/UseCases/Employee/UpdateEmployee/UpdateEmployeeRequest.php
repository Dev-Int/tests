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

use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\PhoneField;

interface UpdateEmployeeRequest
{
    public function uuid(): ResourceUuid;

    public function phone(): PhoneField;

    public function position(): NameField;

    public function department(): NameField;
}
