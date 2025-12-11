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

namespace Admin\Entities\Repository;

use Admin\Entities\Exception\Unit\NoUnitRegistered;
use Admin\Entities\Exception\Unit\UnitNotFound;
use Admin\Entities\Unit\Unit;
use Admin\Entities\Unit\UnitCollection;

interface UnitRepository
{
    public function exists(string $label, string $uuid): bool;

    public function hasUnit(): bool;

    public function save(Unit $unit): void;

    /**
     * @throws UnitNotFound
     */
    public function changeLabel(Unit $unit): void;

    /**
     * @throws NoUnitRegistered
     */
    public function getAllUnits(): UnitCollection;

    /**
     * @throws UnitNotFound
     */
    public function getBySlug(string $slug): Unit;
}
