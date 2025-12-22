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

use Admin\Entities\Exception\ZoneStorage\NoZoneStorageRegistered;
use Admin\Entities\Exception\ZoneStorage\ZoneStorageNotFound;
use Admin\Entities\ZoneStorage\ZoneStorage;
use Admin\Entities\ZoneStorage\ZoneStorageCollection;
use Shared\Entities\ResourceUuid;

interface ZoneStorageRepository
{
    public function exists(string $label): bool;

    public function hasZoneStorage(): bool;

    public function save(ZoneStorage $zoneStorage): void;

    /**
     * @throws ZoneStorageNotFound
     */
    public function changeLabel(ZoneStorage $zoneStorage): void;

    /**
     * @throws ZoneStorageNotFound
     */
    public function changeFamilyLog(ZoneStorage $zoneStorage): void;

    /**
     * @throws NoZoneStorageRegistered
     */
    public function getAllZones(): ZoneStorageCollection;

    /**
     * @throws ZoneStorageNotFound
     */
    public function getBySlug(string $slug): ZoneStorage;

    public function getByUuid(ResourceUuid $uuid): ZoneStorage;
}
