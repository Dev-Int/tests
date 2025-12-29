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
 * DTO for updating article stock quantity.
 * Used when completing inventory to update article quantities.
 */
final readonly class ArticleStockUpdate
{
    public function __construct(
        public string $articleUuid,
        public int $newQuantityMilliemes,
    ) {
    }
}
