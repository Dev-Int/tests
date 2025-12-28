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

namespace Inventory\Adapters\Controller\Symfony\Controller\FinishCounting;

use Inventory\UseCases\FinishCounting\FinishCountingRequest;
use Shared\Entities\ResourceUuid;

final readonly class FinishCountingApiRequest implements FinishCountingRequest
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
