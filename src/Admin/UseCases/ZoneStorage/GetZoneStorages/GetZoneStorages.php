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

namespace Admin\UseCases\ZoneStorage\GetZoneStorages;

use Admin\UseCases\Gateway\ZoneStorageRepository;

final readonly class GetZoneStorages
{
    public function __construct(private ZoneStorageRepository $repository)
    {
    }

    public function execute(): GetZoneStoragesResponse
    {
        $zoneStorages = $this->repository->findAllZone();

        return new GetZoneStoragesResponse($zoneStorages);
    }
}
