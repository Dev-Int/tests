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

namespace Inventory\Adapters\Controller\Symfony\Controller\ReviewInventory;

use Inventory\UseCases\ReviewDiscrepancies\ReviewDiscrepanciesRequest;
use Shared\Entities\ResourceUuid;

final readonly class ReviewInventoryFormRequest implements ReviewDiscrepanciesRequest
{
    /**
     * @param array<string> $selectedItems Format: "articleUuid_zoneStorageUuid"
     */
    public function __construct(
        private ResourceUuid $inventoryUuid,
        private array $selectedItems,
    ) {
    }

    public function inventoryUuid(): ResourceUuid
    {
        return $this->inventoryUuid;
    }

    /**
     * @return array<array{articleUuid: ResourceUuid, zoneStorageUuid: ResourceUuid}>
     */
    public function itemIdentifiers(): array
    {
        return array_map(static function (string $identifier): array {
            [$articleUuid, $zoneStorageUuid] = explode('_', $identifier);

            return [
                'articleUuid' => ResourceUuid::fromString($articleUuid),
                'zoneStorageUuid' => ResourceUuid::fromString($zoneStorageUuid),
            ];
        }, $this->selectedItems);
    }
}
