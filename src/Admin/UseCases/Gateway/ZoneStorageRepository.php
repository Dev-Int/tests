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

use Admin\Entities\ZoneStorage\ZoneStorage;
use Admin\Entities\ZoneStorage\ZoneStorageCollection;

interface ZoneStorageRepository
{
    public function exists(string $label): bool;

    public function hasZoneStorage(): bool;

    public function save(ZoneStorage $zoneStorage): void;

    public function changeLabel(ZoneStorage $zoneStorage): void;

    public function changeFamilyLog(ZoneStorage $zoneStorage): void;

    public function findAllZone(): ZoneStorageCollection;

    public function findBySlug(string $slug): ZoneStorage;
}
