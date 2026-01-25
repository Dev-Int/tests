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

namespace Admin\UseCases\Employee\CreateEmployee;

use Shared\Entities\VO\EmailField;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\PhoneField;

interface CreateEmployeeRequest
{
    public function firstName(): NameField;

    public function lastName(): NameField;

    public function email(): EmailField;

    public function phone(): PhoneField;

    public function position(): NameField;

    public function department(): NameField;

    public function hiredAt(): \DateTimeImmutable;
}
