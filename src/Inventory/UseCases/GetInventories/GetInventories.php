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

namespace Inventory\UseCases\GetInventories;

use Inventory\Entities\InventorySearchCriteria;
use Inventory\Entities\Repository\InventoryRepository;

final readonly class GetInventories
{
    public function __construct(private InventoryRepository $repository)
    {
    }

    public function execute(GetInventoriesRequest $request): GetInventoriesResponse
    {
        $criteria = new InventorySearchCriteria(
            page: $request->page(),
            itemsPerPage: $request->itemsPerPage(),
            status: $request->status(),
            dateAfter: $request->dateAfter(),
            dateBefore: $request->dateBefore(),
            zoneStorageUuid: $request->zoneStorageUuid(),
        );

        return new GetInventoriesResponse($this->repository->findByCriteria($criteria));
    }
}
