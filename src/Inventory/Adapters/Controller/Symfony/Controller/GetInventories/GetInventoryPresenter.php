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

namespace Inventory\Adapters\Controller\Symfony\Controller\GetInventories;

use Inventory\Entities\InventoryCollection;
use Inventory\Entities\VO\ZoneStorage;

final readonly class GetInventoryPresenter
{
    public function __construct(private InventoryCollection $inventories)
    {
    }

    /**
     * @return iterable<InventoryResult>
     */
    public function present(): iterable
    {
        foreach ($this->inventories as $inventory) {
            yield new InventoryResult(
                uuid: $inventory->uuid()->toString(),
                date: $inventory->date()->toDateTimeImmutable()->format('Y-m-d'),
                status: $inventory->status()->value,
                zoneStorageIds: $this->getZoneStorageIds($inventory->zoneStorages()),
            );
        }
    }

    /**
     * @param array<ZoneStorage> $zoneStorages
     *
     * @return array<array{uuid: string, label: string}>
     */
    public function getZoneStorageIds(array $zoneStorages): array
    {
        $zones = [];
        foreach ($zoneStorages as $zoneStorage) {
            $zones[] = [
                'uuid' => $zoneStorage->uuid->toString(),
                'label' => $zoneStorage->name->toString(),
            ];
        }

        return $zones;
    }
}
