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

namespace Admin\UseCases\Gateway;

use Admin\Entities\Exception\Employee\EmployeeAlreadyDisabled;
use Admin\Entities\Exception\Employee\EmployeeNotFound;
use Shared\Entities\ResourceUuid;

interface UserDisablerGateway
{
    /**
     * @throws EmployeeAlreadyDisabled
     * @throws EmployeeNotFound
     */
    public function disableUser(ResourceUuid $userUuid): void;
}
