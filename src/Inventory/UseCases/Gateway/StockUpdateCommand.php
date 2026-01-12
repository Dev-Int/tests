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

use Shared\Entities\ResourceUuid;

/**
 * Commande pour mettre à jour la quantité en stock d'un article.
 *
 * Utilisée par le BC Inventory pour communiquer les mises à jour de stock au BC Admin
 * via le gateway ArticleStockUpdaterInterface.
 */
final readonly class StockUpdateCommand
{
    public function __construct(
        public ResourceUuid $articleUuid,
        public int $newQuantityMilliemes,
    ) {
    }
}
