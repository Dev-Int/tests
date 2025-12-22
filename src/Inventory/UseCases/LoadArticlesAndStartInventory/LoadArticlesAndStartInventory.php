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

namespace Inventory\UseCases\LoadArticlesAndStartInventory;

use Inventory\Entities\Exception\CannotLoadArticlesOnNonDraftInventory;
use Inventory\Entities\Inventory;
use Inventory\Entities\Repository\InventoryRepository;
use Inventory\Entities\VO\ZoneStorage;
use Inventory\UseCases\Gateway\ArticleGateway;

final readonly class LoadArticlesAndStartInventory
{
    public function __construct(
        private InventoryRepository $inventoryRepository,
        private ArticleGateway $articleGateway,
    ) {
    }

    public function execute(StartInventoryRequest $request): StartInventoryResponse
    {
        $inventory = $this->inventoryRepository->getByUuid($request->inventoryUuid());

        if (!$inventory->isDraft()) {
            throw new CannotLoadArticlesOnNonDraftInventory($inventory->status());
        }

        $this->loadArticles($inventory);

        $inventory->startProcessing();

        $this->inventoryRepository->save($inventory);

        return new StartInventoryResponse($inventory);
    }

    private function loadArticles(Inventory $inventory): void
    {
        $zoneUuids = array_map(
            static fn (ZoneStorage $zone) => $zone->uuid,
            $inventory->zoneStorages()
        );

        $articles = $this->articleGateway->provideForZones($zoneUuids);

        $inventory->loadArticles($articles);
    }
}
