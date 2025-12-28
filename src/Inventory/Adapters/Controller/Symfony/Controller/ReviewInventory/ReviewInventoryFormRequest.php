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
     *                                     Note: underscore is safe as separator because RFC 4122 UUIDs
     *                                     only use hyphens (-), not underscores. Do not change this
     *                                     separator without updating the parsing logic in itemIdentifiers().
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
            $parts = explode('_', $identifier);
            if (\count($parts) !== 2) {
                throw new \InvalidArgumentException(
                    \sprintf(
                        'Invalid item identifier format: "%s". Expected "articleUuid_zoneStorageUuid".',
                        $identifier
                    )
                );
            }

            [$articleUuid, $zoneStorageUuid] = $parts;

            return [
                'articleUuid' => ResourceUuid::fromString($articleUuid),
                'zoneStorageUuid' => ResourceUuid::fromString($zoneStorageUuid),
            ];
        }, $this->selectedItems);
    }
}
