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

namespace Inventory\UseCases\ReviewDiscrepancies;

use Shared\Entities\ResourceUuid;

interface ReviewDiscrepanciesRequest
{
    public function inventoryUuid(): ResourceUuid;

    /**
     * @return array<array{articleUuid: ResourceUuid, zoneStorageUuid: ResourceUuid}>
     */
    public function itemIdentifiers(): array;
}
