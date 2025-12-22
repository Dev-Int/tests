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

namespace Admin\UseCases\Gateway\Finder;

use Admin\Entities\ZoneStorage\ZoneStorage;
use Shared\Entities\ResourceUuid;

interface ZoneStorageFinder
{
    public function findByUuid(ResourceUuid|string $uuid): ?ZoneStorage;

    /**
     * @return iterable<ZoneStorage>
     */
    public function findAllZoneStorages(): iterable;
}
