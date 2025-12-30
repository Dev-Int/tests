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

namespace Inventory\UseCases\CancelInventory;

use Inventory\Entities\Repository\InventoryRepository;

final readonly class CancelInventory
{
    public function __construct(
        private InventoryRepository $inventoryRepository,
    ) {
    }

    public function execute(CancelInventoryRequest $request): CancelInventoryResponse
    {
        $inventory = $this->inventoryRepository->getByUuid($request->inventoryUuid());

        $inventory->cancel();

        $this->inventoryRepository->save($inventory);

        return new CancelInventoryResponse(inventory: $inventory);
    }
}
