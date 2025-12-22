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

namespace Admin\Adapters\Gateway\Contracts\Provider\ZoneStorage;

use Admin\Contracts\Services\Provider\Exception\ZoneStorageNotFound;
use Admin\Contracts\Services\Provider\ZoneStorage\Result\ZoneStorageCollectionResult;
use Admin\Contracts\Services\Provider\ZoneStorage\Result\ZoneStorageResult;
use Admin\Contracts\Services\Provider\ZoneStorage\ZoneStorageProvider as ZoneStorageProviderContract;
use Admin\Entities\Repository\ZoneStorageRepository;
use Shared\Entities\ResourceUuid;

final readonly class ZoneStorageProvider implements ZoneStorageProviderContract
{
    public function __construct(private ZoneStorageRepository $repository)
    {
    }

    public function provide(ResourceUuid $uuid): ZoneStorageResult
    {
        try {
            $zoneStorage = $this->repository->getByUuid($uuid);
        } catch (\Throwable) {
            throw new ZoneStorageNotFound($uuid->toString());
        }

        return new ZoneStorageResult($uuid, $zoneStorage->label(), $zoneStorage->slug());
    }

    public function provideAll(?iterable $ids = null): ZoneStorageCollectionResult
    {
        $zoneStorages = new ZoneStorageCollectionResult(0);
        if (null === $ids) {
            $zoneStoragesOrm = $this->repository->getAllZones();
            foreach ($zoneStoragesOrm as $zoneStorage) {
                $zoneStorages->add(
                    new ZoneStorageResult($zoneStorage->uuid(), $zoneStorage->label(), $zoneStorage->slug())
                );
            }
        } else {
            foreach ($ids as $id) {
                $zoneStorages->add($this->provide($id));
            }
        }

        return $zoneStorages;
    }
}
