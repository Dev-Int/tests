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

namespace Admin\Adapters\Controller\Symfony\Controller\Employee\CreateEmployee;

use Admin\UseCases\Employee\CreateEmployee\CreateEmployeeRequest;
use Shared\Entities\VO\EmailField;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\PhoneField;

final class CreateEmployeeApiRequest implements CreateEmployeeRequest
{
    public function __construct(
        public NameField $firstName,
        public NameField $lastName,
        public EmailField $email,
        public PhoneField $phone,
        public NameField $position,
        public NameField $department,
        public \DateTimeImmutable $hiredAt,
    ) {
    }

    public function firstName(): NameField
    {
        return $this->firstName;
    }

    public function lastName(): NameField
    {
        return $this->lastName;
    }

    public function email(): EmailField
    {
        return $this->email;
    }

    public function phone(): PhoneField
    {
        return $this->phone;
    }

    public function position(): NameField
    {
        return $this->position;
    }

    public function department(): NameField
    {
        return $this->department;
    }

    public function hiredAt(): \DateTimeImmutable
    {
        return $this->hiredAt;
    }
}
