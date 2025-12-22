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

namespace Inventory\UseCases\LoadArticlesIntoInventory;

use Inventory\Entities\Exception\CannotLoadArticlesOnNonDraftInventory;
use Inventory\Entities\InventoryItem;
use Inventory\Entities\Repository\InventoryRepository;
use Inventory\Entities\VO\ZoneStorage;
use Inventory\UseCases\Gateway\ArticleGateway;

final readonly class LoadArticlesIntoInventory
{
    public function __construct(
        private InventoryRepository $inventoryRepository,
        private ArticleGateway $articleGateway,
    ) {
    }

    public function execute(LoadArticlesIntoInventoryRequest $request): LoadArticlesIntoInventoryResponse
    {
        $inventory = $this->inventoryRepository->getByUuid($request->inventoryUuid());

        if (!$inventory->isDraft()) {
            throw new CannotLoadArticlesOnNonDraftInventory($inventory->status());
        }

        $inventory->clearItems();

        $zoneUuids = array_map(
            static fn (ZoneStorage $zone) => $zone->uuid,
            $inventory->zoneStorages()
        );

        $itemsLoaded = 0;
        $articles = $this->articleGateway->provideForZones($zoneUuids);
        foreach ($articles as $article) {
            $inventory->addItem(InventoryItem::createFromArticle($article));
            ++$itemsLoaded;
        }

        $this->inventoryRepository->save($inventory);

        return new LoadArticlesIntoInventoryResponse($inventory, $itemsLoaded);
    }
}
