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
use Admin\UseCases\Gateway\TransactionGateway;
use Admin\UseCases\Gateway\UserDisablerGateway;

final readonly class DisableEmployee
{
    public function __construct(
        private TransactionGateway $transactionGateway,
        private EmployeeRepository $repository,
        private UserDisablerGateway $userDisabler,
    ) {
    }

    public function execute(DisableEmployeeRequest $request): DisableEmployeeResponse
    {
        return $this->transactionGateway->wrapInTransaction(
            operation: $this->disableEmployee($request),
        );
    }

    private function disableEmployee(DisableEmployeeRequest $request): \Closure
    {
        return function () use ($request): DisableEmployeeResponse {
            $employee = $this->repository->getByUuid($request->uuid());

            $this->userDisabler->disableUser($employee->userUuid());

            $employee->disable();

            $this->repository->update($employee);

            return new DisableEmployeeResponse($employee);
        };
    }
}
