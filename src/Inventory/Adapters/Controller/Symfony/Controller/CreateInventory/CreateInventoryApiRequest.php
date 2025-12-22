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

namespace Inventory\Adapters\Controller\Symfony\Controller\CreateInventory;

use Inventory\Entities\VO\ZoneStorage;
use Inventory\UseCases\CreateInventory\CreateInventoryRequest;
use Shared\Entities\ResourceUuid;

final class CreateInventoryApiRequest implements CreateInventoryRequest
{
    /**
     * @param array<ZoneStorage> $zoneStorages
     */
    public function __construct(
        public ResourceUuid $uuid,
        public \DateTimeImmutable $date,
        public array $zoneStorages
    ) {
    }

    public function uuid(): ResourceUuid
    {
        return $this->uuid;
    }

    public function date(): \DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @return array<ZoneStorage>
     */
    public function zoneStorages(): array
    {
        return $this->zoneStorages;
    }
}
