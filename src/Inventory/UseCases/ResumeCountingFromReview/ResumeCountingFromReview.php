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

namespace Inventory\UseCases\ResumeCountingFromReview;

use Inventory\Entities\Repository\InventoryRepository;

final readonly class ResumeCountingFromReview
{
    public function __construct(
        private InventoryRepository $inventoryRepository,
    ) {
    }

    public function execute(ResumeCountingFromReviewRequest $request): ResumeCountingFromReviewResponse
    {
        $inventory = $this->inventoryRepository->getByUuid($request->inventoryUuid());

        $inventory->resumeCounting($request->zoneStorageUuid());

        $this->inventoryRepository->save($inventory);

        return new ResumeCountingFromReviewResponse(inventory: $inventory);
    }
}
