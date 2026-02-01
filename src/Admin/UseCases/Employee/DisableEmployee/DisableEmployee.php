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

use Admin\Entities\Repository\EmployeeRepository;
use Admin\UseCases\Gateway\UserDisablerGateway;

final readonly class DisableEmployee
{
    public function __construct(
        private EmployeeRepository $repository,
        private UserDisablerGateway $userDisabler,
    ) {
    }

    public function execute(DisableEmployeeRequest $request): DisableEmployeeResponse
    {
        $employee = $this->repository->getByUuid($request->uuid());

        $employee->disable();

        $this->userDisabler->disableUser($employee->userUuid());

        $this->repository->update($employee);

        return new DisableEmployeeResponse($employee);
    }
}
