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

namespace Inventory\UseCases\FinishCounting;

use Inventory\Entities\Repository\InventoryRepository;

final readonly class FinishCounting
{
    public function __construct(
        private InventoryRepository $inventoryRepository,
    ) {
    }

    public function execute(FinishCountingRequest $request): FinishCountingResponse
    {
        $inventory = $this->inventoryRepository->getByUuid($request->inventoryUuid());

        $inventory->finishCounting();

        $this->inventoryRepository->save($inventory);

        $itemsWithDiscrepancies = $inventory->items()->getItemsWithDiscrepancies();

        return new FinishCountingResponse(
            inventory: $inventory,
            discrepancyCount: \count($itemsWithDiscrepancies),
            itemsWithDiscrepancies: $itemsWithDiscrepancies,
        );
    }
}
