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

namespace Inventory\UseCases\GetInventories;

use Inventory\Entities\VO\InventoryStatus;
use Shared\Entities\ResourceUuid;

interface GetInventoriesRequest
{
    public function page(): int;

    public function itemsPerPage(): int;

    public function status(): ?InventoryStatus;

    public function dateAfter(): ?\DateTimeImmutable;

    public function dateBefore(): ?\DateTimeImmutable;

    public function zoneStorageUuid(): ?ResourceUuid;
}
