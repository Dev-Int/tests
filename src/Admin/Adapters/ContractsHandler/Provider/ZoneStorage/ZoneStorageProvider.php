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

namespace Admin\Adapters\ContractsHandler\Provider\ZoneStorage;

use Admin\Contracts\Services\Provider\Exception\ZoneStorageNotFound;
use Admin\Contracts\Services\Provider\ZoneStorage\Result\ZoneStorageCollectionResult;
use Admin\Contracts\Services\Provider\ZoneStorage\Result\ZoneStorageResult;
use Admin\Contracts\Services\Provider\ZoneStorage\ZoneStorageProvider as ZoneStorageProviderContract;
use Admin\Entities\ZoneStorage\ZoneStorage;
use Admin\UseCases\Gateway\Finder\ZoneStorageFinder;
use Shared\Entities\ResourceUuid;

final class ZoneStorageProvider implements ZoneStorageProviderContract
{
    /**
     * Cache instance-based pour éviter les requêtes N+1 au sein d'une même requête HTTP.
     *
     * Note: Ce cache persiste pour la durée de vie du service. En environnement synchrone
     * (une requête = une instance), c'est acceptable. Si le service devient partagé entre
     * requêtes (workers, async), considérer une invalidation TTL ou event-based.
     *
     * @var array<string, ZoneStorageResult>
     */
    private array $cache = [];

    public function __construct(private readonly ZoneStorageFinder $finder)
    {
    }

    public function provide(ResourceUuid $uuid): ZoneStorageResult
    {
        $key = $uuid->toString();

        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $zoneStorage = $this->finder->findByUuid($uuid);

        if (!$zoneStorage instanceof ZoneStorage) {
            throw new ZoneStorageNotFound($key);
        }

        return $this->cache[$key] = new ZoneStorageResult($uuid, $zoneStorage->label(), $zoneStorage->slug());
    }

    public function provideAll(?iterable $ids = null): ZoneStorageCollectionResult
    {
        $zoneStorages = new ZoneStorageCollectionResult(0);

        if ($ids === null) {
            foreach ($this->finder->findAllZoneStorages() as $zoneStorage) {
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
