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

namespace Admin\UseCases\Unit\GetUnits;

use Admin\UseCases\Gateway\UnitRepository;

final readonly class GetUnits
{
    public function __construct(private UnitRepository $unitRepository)
    {
    }

    public function execute(): GetUnitsResponse
    {
        $units = $this->unitRepository->findAllUnits();

        return new GetUnitsResponse($units);
    }
}
