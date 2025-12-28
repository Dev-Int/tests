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

namespace Inventory\Adapters\Controller\Symfony\Controller\RecordRealStockForZone;

use Inventory\Entities\InventoryItem;
use Inventory\Entities\VO\PackagingLevel;
use Inventory\Entities\VO\PackagingSnapshot;

final readonly class RecordRealStockForZonePresenter
{
    /**
     * @param array<InventoryItem> $items
     */
    public function __construct(
        private array $items,
    ) {
    }

    /**
     * @return array<InventoryItemResult>
     */
    public function present(): array
    {
        $results = [];
        foreach ($this->items as $item) {
            $components = $item->realStockComponents();
            $results[] = new InventoryItemResult(
                articleUuid: $item->article()->toString(),
                articleName: $item->articleName()->toString(),
                articleSlug: $item->articleName()->slugify(),
                theoreticalStock: $item->theoreticalStock()->toUnit(),
                realStock: $item->realStock()->toUnit(),
                realStockParcel: $components->parcel->toUnit(),
                realStockSubPackage: $components->subPackage->toUnit(),
                realStockConsumerUnit: $components->consumerUnit->toUnit(),
                packaging: $this->mapPackaging($item->packaging()),
            );
        }

        return $results;
    }

    private function mapPackaging(PackagingSnapshot $packaging): PackagingForView
    {
        $parcel = new PackagingLevelForView(
            $packaging->parcel->unitLabel,
            $packaging->parcel->unitAbbreviation,
        );

        $subPackage = null;
        if ($packaging->subPackage instanceof PackagingLevel) {
            $subPackage = new PackagingLevelForView(
                $packaging->subPackage->unitLabel,
                $packaging->subPackage->unitAbbreviation,
            );
        }

        $consumerUnit = null;
        if ($packaging->consumerUnit instanceof PackagingLevel) {
            $consumerUnit = new PackagingLevelForView(
                $packaging->consumerUnit->unitLabel,
                $packaging->consumerUnit->unitAbbreviation,
            );
        }

        return new PackagingForView($parcel, $subPackage, $consumerUnit);
    }
}
