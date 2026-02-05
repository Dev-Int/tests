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

use Admin\Entities\Repository\EmployeeRepository;
use Admin\UseCases\Gateway\TransactionGateway;

final readonly class UpdateEmployee
{
    public function __construct(
        private EmployeeRepository $repository,
        private TransactionGateway $transactionGateway,
    ) {
    }

    public function execute(UpdateEmployeeRequest $request): UpdateEmployeeResponse
    {
        return $this->transactionGateway->wrapInTransaction(
            operation: $this->updateEmployee($request),
        );
    }

    private function updateEmployee(UpdateEmployeeRequest $request): \Closure
    {
        return function () use ($request): UpdateEmployeeResponse {
            $employee = $this->repository->getByUuid($request->uuid());

            $employee->updatePhone($request->phone());

            $employee->updatePosition(
                $request->position(),
                $request->department(),
            );

            $this->repository->update($employee);

            return new UpdateEmployeeResponse($employee);
        };
    }
}
