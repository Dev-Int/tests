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

use Inventory\Entities\VO\InventoryStatus;
use Inventory\UseCases\GetInventories\GetInventoriesRequest;
use Shared\Entities\ResourceUuid;

final class GetInventoriesApiRequest implements GetInventoriesRequest
{
    public function __construct(
        public int $page,
        public int $itemsPerPage,
        private readonly ?string $status = null,
        private readonly ?\DateTimeImmutable $dateAfter = null,
        private readonly ?\DateTimeImmutable $dateBefore = null,
        private readonly ?string $zoneStorage = null,
    ) {
    }

    public function page(): int
    {
        return $this->page;
    }

    public function itemsPerPage(): int
    {
        return $this->itemsPerPage;
    }

    public function status(): ?InventoryStatus
    {
        if ($this->status === null || $this->status === '') {
            return null;
        }

        return InventoryStatus::tryFrom($this->status);
    }

    public function dateAfter(): ?\DateTimeImmutable
    {
        return $this->dateAfter;
    }

    public function dateBefore(): ?\DateTimeImmutable
    {
        return $this->dateBefore;
    }

    public function zoneStorageUuid(): ?ResourceUuid
    {
        if ($this->zoneStorage === null || $this->zoneStorage === '') {
            return null;
        }

        return ResourceUuid::fromString($this->zoneStorage);
    }
}
