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

namespace Inventory\UseCases\CompleteInventory;

use Inventory\Entities\Inventory;
use Shared\Entities\VO\Amount;

final readonly class CompleteInventoryResponse
{
    public function __construct(
        public Inventory $inventory,
        public int $articlesUpdated,
        public Amount $discrepancyAmount,
    ) {
    }
}
