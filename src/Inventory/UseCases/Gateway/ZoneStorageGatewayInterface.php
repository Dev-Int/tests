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

namespace Inventory\UseCases\Gateway;

use Inventory\Entities\VO\ZoneStorage;
use Shared\Entities\ResourceUuid;

interface ZoneStorageGatewayInterface
{
    /**
     * @param iterable<ResourceUuid>|null $ids
     *
     * @return array<ZoneStorage>
     */
    public function provideAll(?iterable $ids = null): array;

    public function provide(ResourceUuid $fromString): ZoneStorage;
}
