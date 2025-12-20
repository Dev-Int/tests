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

namespace Inventory\UseCases\CreateInventory;

use Inventory\Entities\Exception\EqualOrFutureDateExpected;
use Inventory\Entities\Exception\InventoryAlreadyActiveForZone;
use Inventory\Entities\Inventory;
use Inventory\Entities\ReadModel\ZoneStorage;
use Inventory\Entities\Repository\InventoryRepository;
use Shared\Entities\Clock\ClockFactory;

final readonly class CreateInventory
{
    public function __construct(private InventoryRepository $inventoryRepository)
    {
    }

    public function execute(CreateInventoryRequest $request): CreateInventoryResponse
    {
        $date = $request->date();
        $now = ClockFactory::clock()->now();
        if ($date < $now) {
            throw new EqualOrFutureDateExpected($date);
        }
        $zoneStorageIds = array_map(
            static fn (ZoneStorage $zoneStorage) => $zoneStorage->uuid,
            $request->zoneStorages()
        );
        $hasActive = $this->inventoryRepository->hasActiveForZone($zoneStorageIds);
        if ($hasActive) {
            throw new InventoryAlreadyActiveForZone($zoneStorageIds);
        }

        $inventory = Inventory::create(
            $request->uuid(),
            $request->zoneStorages(),
            $date
        );

        $this->inventoryRepository->save($inventory);

        return new CreateInventoryResponse($inventory);
    }
}
