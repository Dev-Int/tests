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

namespace Inventory\UseCases\CreateInventory;

use Shared\Entities\ResourceUuid;

interface CreateInventoryRequest
{
    public function uuid(): ResourceUuid;

    public function date(): \DateTimeImmutable;

    /**
     * @return array<ResourceUuid>
     */
    public function zoneStorages(): array;
}
