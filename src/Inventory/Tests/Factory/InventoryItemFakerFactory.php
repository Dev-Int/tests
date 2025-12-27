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

namespace Inventory\Tests\Factory;

use Inventory\Tests\DataBuilder\InventoryItemDataBuilder;
use Shared\Entities\ResourceUuid;

final class InventoryItemFakerFactory
{
    public function create(
        ?ResourceUuid $article = null,
        ?ResourceUuid $zoneStorage = null,
    ): InventoryItemDataBuilder {
        return new InventoryItemDataBuilder(
            article: $article,
            zoneStorage: $zoneStorage,
        );
    }

    public function createWithRealStock(
        float $realStock,
        ?ResourceUuid $article = null,
        ?ResourceUuid $zoneStorage = null,
    ): InventoryItemDataBuilder {
        return (new InventoryItemDataBuilder(
            article: $article,
            zoneStorage: $zoneStorage,
        ))->withRealStock($realStock);
    }

    public function createWithPreciseStocks(
        float $theoreticalStock,
        float $realStock,
        ?ResourceUuid $zoneStorage = null,
    ): InventoryItemDataBuilder {
        return (new InventoryItemDataBuilder(zoneStorage: $zoneStorage))
            ->withPrice(1000)
            ->withTheoreticalStock($theoreticalStock)
            ->withRealStock($realStock)
            ->withAmount(5000)
            ->asCounted()
        ;
    }
}
