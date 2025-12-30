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

namespace Inventory\UseCases\CompleteInventory;

use Inventory\Entities\Inventory;
use Inventory\Entities\Repository\InventoryRepository;
use Inventory\UseCases\Gateway\ArticleStockUpdaterInterface;
use Inventory\UseCases\Gateway\StockUpdateCommand;
use Shared\Entities\Persistence\TransactionalExecutorInterface;
use Shared\Entities\VO\Amount;

final readonly class CompleteInventory
{
    public function __construct(
        private InventoryRepository $inventoryRepository,
        private ArticleStockUpdaterInterface $articleStockUpdater,
        private TransactionalExecutorInterface $transactionalExecutor,
    ) {
    }

    public function execute(CompleteInventoryRequest $request): CompleteInventoryResponse
    {
        return $this->transactionalExecutor->execute(function () use ($request): CompleteInventoryResponse {
            $inventory = $this->inventoryRepository->getByUuid($request->inventoryUuid());

            $discrepancyAmount = $this->calculateDiscrepancyAmount($inventory);

            $inventory->complete($discrepancyAmount);

            $updates = $this->aggregateStocksByArticle($inventory);
            $this->articleStockUpdater->updateStocks($updates);

            $this->inventoryRepository->save($inventory);

            return new CompleteInventoryResponse(
                inventory: $inventory,
                articlesUpdated: \count($updates),
                discrepancyAmount: $discrepancyAmount,
            );
        });
    }

    /**
     * @return array<StockUpdateCommand>
     */
    private function aggregateStocksByArticle(Inventory $inventory): array
    {
        $updates = [];

        foreach ($inventory->items()->getArticleUuids() as $articleUuid) {
            $totalStock = $inventory->items()->getTotalRealStockForArticle($articleUuid);
            $updates[] = new StockUpdateCommand(
                articleUuid: $articleUuid,
                newQuantityMilliemes: $totalStock->toMilliemes(),
            );
        }

        return $updates;
    }

    /**
     * Calculate total discrepancy amount: Σ (realStock - theoreticalStock) × unitPrice.
     *
     * Positive = surplus (gain), Negative = shortage (loss).
     */
    private function calculateDiscrepancyAmount(Inventory $inventory): Amount
    {
        $total = Amount::zero();

        foreach ($inventory->items() as $item) {
            $difference = $item->calculateDifference();
            $itemAmount = $item->price()->computeQuantity($difference);
            $total = $total->add($itemAmount);
        }

        return $total;
    }
}
