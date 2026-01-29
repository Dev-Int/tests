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

final readonly class UpdateEmployee
{
    public function __construct(
        private EmployeeRepository $repository,
    ) {
    }

    public function execute(UpdateEmployeeRequest $request): UpdateEmployeeResponse
    {
        $employee = $this->repository->getByUuid($request->uuid());

        // Vérifier si les informations de contact ont changé
        $contactInformation = $request->contactInformation();
        $contactInfoChanged = $employee->contactInformation()->email->toString() !== $contactInformation->email->toString()
            || $employee->contactInformation()->phone->toNumber() !== $contactInformation->phone->toNumber();

        if ($contactInfoChanged) {
            $employee->updateContactInfo(
                $contactInformation->email,
                $contactInformation->phone,
            );
        }

        // Vérifier si la position ou le département ont changé
        $position = $request->position();
        $positionChanged = $employee->position()->toString() !== $position->toString()
            || $employee->department()->toString() !== $request->department()->toString();

        if ($positionChanged) {
            $employee->updatePosition(
                $position,
                $request->department(),
            );
        }

        // Vérifier si le statut a changé
        $status = $request->status();
        if ($employee->status() !== $status) {
            $employee->changeStatus($status);
        }

        $this->repository->update($employee);

        return new UpdateEmployeeResponse($employee);
    }
}
