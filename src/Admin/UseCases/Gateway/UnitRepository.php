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

use Admin\Entities\Unit;
use Admin\Entities\Unit\UnitCollection;

interface UnitRepository
{
    public function exists(string $label): bool;

    public function save(Unit $unit): void;

    public function hasUnit(): bool;

    public function findAllUnits(): UnitCollection;
}
