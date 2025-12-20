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

namespace Inventory\UseCase\GetInventories;

use Inventory\Entities\Repository\InventoryRepository;

final readonly class GetInventories
{
    public function __construct(private InventoryRepository $repository)
    {
    }

    public function execute(): GetInventoriesResponse
    {
        $inventories = $this->repository->getAllInventories();

        return new GetInventoriesResponse($inventories);
    }
}
