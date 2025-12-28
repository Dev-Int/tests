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

namespace Inventory\Adapters\Gateway;

use Admin\Contracts\Services\Provider\ZoneStorage\ZoneStorageProvider;
use Inventory\Entities\VO\ZoneStorage;
use Inventory\UseCases\Gateway\ZoneStorageGatewayInterface;
use Shared\Entities\ResourceUuid;

final readonly class ZoneStorageGateway implements ZoneStorageGatewayInterface
{
    public function __construct(private ZoneStorageProvider $zoneStorageProvider)
    {
    }

    public function provide(ResourceUuid $fromString): ZoneStorage
    {
        $zoneStorage = $this->zoneStorageProvider->provide($fromString);

        return new ZoneStorage(
            $zoneStorage->uuid,
            $zoneStorage->label,
        );
    }

    public function provideAll(?iterable $ids = null): array
    {
        $zoneStorages = $this->zoneStorageProvider->provideAll($ids);
        $collection = [];

        foreach ($zoneStorages as $zoneStorage) {
            $collection[] = new ZoneStorage(
                $zoneStorage->uuid,
                $zoneStorage->label,
            );
        }

        return $collection;
    }
}
