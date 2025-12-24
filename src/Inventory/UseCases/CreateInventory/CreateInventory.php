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

use Inventory\Entities\Exception\InventoryAlreadyActiveForZone;
use Inventory\Entities\Inventory;
use Inventory\Entities\Repository\InventoryRepository;
use Inventory\Entities\VO\InventoryDate;
use Inventory\Entities\VO\ZoneStorage;
use Inventory\UseCases\Gateway\ZoneStorageGatewayInterface;

final readonly class CreateInventory
{
    public function __construct(
        private InventoryRepository $inventoryRepository,
        private ZoneStorageGatewayInterface $zoneStorageGateway,
    ) {
    }

    public function execute(CreateInventoryRequest $request): CreateInventoryResponse
    {
        $inventoryDate = InventoryDate::fromDateTimeImmutable($request->date());
        $zoneStorages = $request->zoneStorages();
        $zoneStorageIds = array_map(
            static fn (ZoneStorage $zoneStorage) => $zoneStorage->uuid,
            $zoneStorages
        );
        if ($zoneStorages === []) {
            $zoneStorages = $this->zoneStorageGateway->provideAll();
            $zoneStorageIds = array_map(
                static fn (ZoneStorage $zone) => $zone->uuid,
                $zoneStorages
            );
        }
        $hasActive = $this->inventoryRepository->hasActiveForZone($zoneStorageIds);
        if ($hasActive) {
            throw new InventoryAlreadyActiveForZone($zoneStorageIds);
        }
        $inventory = Inventory::create(
            $request->uuid(),
            $zoneStorages,
            $inventoryDate
        );

        $this->inventoryRepository->save($inventory);

        return new CreateInventoryResponse($inventory);
    }
}
