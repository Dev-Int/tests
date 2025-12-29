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

namespace Admin\Contracts\Services\Updater\Article;

/**
 * Contract for updating article quantities.
 * Used by Inventory BC to update article stock after completing an inventory.
 */
interface ArticleQuantityUpdater
{
    /**
     * Update quantities for multiple articles.
     *
     * @param array<ArticleStockUpdate> $updates
     */
    public function updateQuantities(array $updates): void;
}
