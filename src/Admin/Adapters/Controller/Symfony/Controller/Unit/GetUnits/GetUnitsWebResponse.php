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

namespace Admin\Adapters\Controller\Symfony\Controller\Unit\GetUnits;

use Admin\UseCases\Unit\GetUnits\GetUnitsResponse;

final class GetUnitsWebResponse
{
    /** @var array<UnitDto> */
    private array $units = [];

    public function __construct(GetUnitsResponse $response)
    {
        foreach ($response->units as $unit) {
            $this->units[] = new UnitDto(
                $unit->label()->toString(),
                $unit->abbreviation(),
                $unit->slug()
            );
        }
    }

    /**
     * @return array<UnitDto>
     */
    public function units(): array
    {
        return $this->units;
    }
}
