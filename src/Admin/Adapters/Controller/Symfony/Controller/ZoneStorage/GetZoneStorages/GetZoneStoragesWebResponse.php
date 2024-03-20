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

namespace Admin\Adapters\Controller\Symfony\Controller\ZoneStorage\GetZoneStorages;

use Admin\UseCases\ZoneStorage\GetZoneStorages\GetZoneStoragesResponse;

final class GetZoneStoragesWebResponse
{
    /** @var array<ZoneStorageDto> */
    private array $zoneStorages = [];

    public function __construct(GetZoneStoragesResponse $response)
    {
        foreach ($response->zoneStorages as $zoneStorage) {
            $this->zoneStorages[] = new ZoneStorageDto(
                $zoneStorage->slug(),
                $zoneStorage->label()->toString(),
                $zoneStorage->familyLog()->label()->toString()
            );
        }
    }

    /**
     * @return array<ZoneStorageDto>
     */
    public function zoneStorages(): array
    {
        return $this->zoneStorages;
    }
}
