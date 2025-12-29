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

namespace Inventory\Adapters\Gateway;

use Admin\Contracts\Services\Updater\Article\ArticleQuantityUpdater;
use Admin\Contracts\Services\Updater\Article\ArticleStockUpdate;
use Inventory\UseCases\Gateway\ArticleStockUpdaterInterface;
use Inventory\UseCases\Gateway\StockUpdateCommand;

/**
 * Implementation of ArticleStockUpdaterInterface that delegates to Admin BC.
 *
 * This adapter translates Inventory domain commands into Admin BC contract calls.
 */
final readonly class ArticleStockUpdater implements ArticleStockUpdaterInterface
{
    public function __construct(
        private ArticleQuantityUpdater $articleQuantityUpdater,
    ) {
    }

    public function updateStocks(array $commands): void
    {
        $updates = array_map(
            static fn (StockUpdateCommand $command): ArticleStockUpdate => new ArticleStockUpdate(
                articleUuid: $command->articleUuid->toString(),
                newQuantityMilliemes: $command->newQuantityMilliemes,
            ),
            $commands
        );

        $this->articleQuantityUpdater->updateQuantities($updates);
    }
}
