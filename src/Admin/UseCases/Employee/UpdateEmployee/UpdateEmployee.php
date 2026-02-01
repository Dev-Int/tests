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

        // Mettre à jour les informations de contact (email est immutable)
        $contactInformation = $request->contactInformation();
        $employee->updateContactInfo(
            $employee->contactInformation()->email(), // Email immutable, on garde l'ancien
            $contactInformation->phone(),
        );

        // Mettre à jour la position et le département
        $employee->updatePosition(
            $request->position(),
            $request->department(),
        );

        $this->repository->updateContactInfo($employee);
        $this->repository->updatePosition($employee);

        return new UpdateEmployeeResponse($employee);
    }
}
