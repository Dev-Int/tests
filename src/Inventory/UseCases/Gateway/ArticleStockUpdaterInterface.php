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

namespace Inventory\UseCases\Gateway;

/**
 * Gateway interface for updating article stock quantities.
 *
 * This interface abstracts the communication with the Admin BC for stock updates.
 * The implementation in Adapters\Gateway uses Admin\Contracts to perform the actual update.
 */
interface ArticleStockUpdaterInterface
{
    /**
     * Updates the stock quantities for multiple articles.
     *
     * @param array<StockUpdateCommand> $commands List of stock update commands
     */
    public function updateStocks(array $commands): void;
}
