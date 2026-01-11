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

namespace Inventory\Entities;

use Inventory\Entities\VO\InventoryStatus;
use Shared\Entities\ResourceUuid;

/**
 * Critères de recherche pour filtrer et paginer les inventaires.
 */
final readonly class InventorySearchCriteria
{
    public const int DEFAULT_PAGE = 1;
    public const int DEFAULT_ITEMS_PER_PAGE = 10;

    public function __construct(
        public int $page = self::DEFAULT_PAGE,
        public int $itemsPerPage = self::DEFAULT_ITEMS_PER_PAGE,
        public ?InventoryStatus $status = null,
        public ?\DateTimeImmutable $dateAfter = null,
        public ?\DateTimeImmutable $dateBefore = null,
        public ?ResourceUuid $zoneStorageUuid = null,
    ) {
    }
}
