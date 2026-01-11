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
 * Interface passerelle pour la mise à jour des quantités en stock des articles.
 *
 * Cette interface abstrait la communication avec Admin BC pour les mises à jour des stocks.
 */
interface ArticleStockUpdaterInterface
{
    /**
     * @param array<StockUpdateCommand> $commands
     */
    public function updateStocks(array $commands): void;
}
