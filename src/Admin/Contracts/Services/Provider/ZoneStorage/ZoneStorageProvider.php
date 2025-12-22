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

namespace Admin\Contracts\Services\Provider\ZoneStorage;

use Admin\Contracts\Services\Provider\Exception\ZoneStorageNotFound;
use Admin\Contracts\Services\Provider\ZoneStorage\Result\ZoneStorageCollectionResult;
use Admin\Contracts\Services\Provider\ZoneStorage\Result\ZoneStorageResult;
use Shared\Entities\ResourceUuid;

interface ZoneStorageProvider
{
    public const string DB_ALIAS = 'zone_storage';

    /**
     * @throws ZoneStorageNotFound
     */
    public function provide(ResourceUuid $uuid): ZoneStorageResult;

    /**
     * @param iterable<ResourceUuid>|null $ids
     *
     * @throws ZoneStorageNotFound
     */
    public function provideAll(?iterable $ids = null): ZoneStorageCollectionResult;
}
