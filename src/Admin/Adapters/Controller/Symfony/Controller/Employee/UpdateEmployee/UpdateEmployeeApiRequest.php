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

namespace Admin\Adapters\Controller\Symfony\Controller\Employee\UpdateEmployee;

use Admin\Entities\Employee\ContactInformation;
use Admin\UseCases\Employee\UpdateEmployee\UpdateEmployeeRequest;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\EmailField;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\PhoneField;

final readonly class UpdateEmployeeApiRequest implements UpdateEmployeeRequest
{
    public function __construct(
        private ResourceUuid $uuid,
        private EmailField $email,
        private PhoneField $phone,
        private NameField $position,
        private NameField $department,
    ) {
    }

    public function uuid(): ResourceUuid
    {
        return $this->uuid;
    }

    public function contactInformation(): ContactInformation
    {
        return ContactInformation::fromFields($this->email, $this->phone);
    }

    public function position(): NameField
    {
        return $this->position;
    }

    public function department(): NameField
    {
        return $this->department;
    }
}
