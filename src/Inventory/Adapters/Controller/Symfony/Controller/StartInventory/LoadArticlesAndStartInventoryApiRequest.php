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

namespace Inventory\Adapters\Controller\Symfony\Controller\StartInventory;

use Inventory\UseCases\LoadArticlesAndStartInventory\LoadArticlesAndStartInventoryRequest;
use Shared\Entities\ResourceUuid;

final readonly class LoadArticlesAndStartInventoryApiRequest implements LoadArticlesAndStartInventoryRequest
{
    public function __construct(
        private ResourceUuid $inventoryUuid,
    ) {
    }

    public function inventoryUuid(): ResourceUuid
    {
        return $this->inventoryUuid;
    }
}
