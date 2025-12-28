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

namespace Inventory\UseCases\ReviewDiscrepancies;

use Inventory\Entities\Repository\InventoryRepository;

final readonly class ReviewDiscrepancies
{
    public function __construct(
        private InventoryRepository $inventoryRepository,
    ) {
    }

    public function execute(ReviewDiscrepanciesRequest $request): ReviewDiscrepanciesResponse
    {
        $inventory = $this->inventoryRepository->getByUuid($request->inventoryUuid());

        $reviewedItems = $inventory->reviewDiscrepancies($request->itemIdentifiers());

        $this->inventoryRepository->save($inventory);

        return new ReviewDiscrepanciesResponse(
            inventory: $inventory,
            reviewedItems: $reviewedItems,
            reviewedCount: \count($reviewedItems),
        );
    }
}
