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

namespace Inventory\Entities\Repository;

use Inventory\Entities\Inventory;
use Inventory\UseCase\GetInventories\InventoryCollection;
use Shared\Entities\ResourceUuid;

interface InventoryRepository
{
    /**
     * @param array<ResourceUuid> $zoneStorageIds
     */
    public function hasActiveForZone(array $zoneStorageIds): bool;

    public function save(Inventory $inventory): void;

    public function getAllInventories(): InventoryCollection;
}
