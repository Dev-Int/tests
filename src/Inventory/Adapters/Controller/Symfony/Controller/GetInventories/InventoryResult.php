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

namespace Inventory\Adapters\Controller\Symfony\Controller\GetInventories;

final class InventoryResult
{
    /**
     * @param array<array{uuid: string, label: string}> $zoneStorageIds
     */
    public function __construct(
        public string $uuid,
        public string $date,
        public string $status,
        public array $zoneStorageIds,
        public bool $allItemsCounted,
        public bool $hasUnreviewedDiscrepancies,
    ) {
    }
}
