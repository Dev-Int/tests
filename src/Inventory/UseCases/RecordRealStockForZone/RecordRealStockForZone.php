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

namespace Inventory\UseCases\RecordRealStockForZone;

use Inventory\Entities\Repository\InventoryRepository;

final readonly class RecordRealStockForZone
{
    public function __construct(
        private InventoryRepository $inventoryRepository,
    ) {
    }

    public function execute(RecordRealStockForZoneRequest $request): RecordRealStockForZoneResponse
    {
        $inventory = $this->inventoryRepository->getByUuid($request->inventoryUuid());

        $items = $inventory->recordRealStocks(
            $request->articlesData(),
            $request->zoneStorageUuid(),
        );

        $this->inventoryRepository->save($inventory);

        return new RecordRealStockForZoneResponse($items);
    }
}
