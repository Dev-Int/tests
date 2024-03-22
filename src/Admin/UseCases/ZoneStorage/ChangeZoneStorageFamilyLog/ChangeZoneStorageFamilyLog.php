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

namespace Admin\UseCases\ZoneStorage\ChangeZoneStorageFamilyLog;

use Admin\UseCases\Gateway\ZoneStorageRepository;

final readonly class ChangeZoneStorageFamilyLog
{
    public function __construct(private ZoneStorageRepository $zoneStorageRepository)
    {
    }

    public function execute(ChangeZoneStorageFamilyLogRequest $request): ChangeZoneStorageFamilyLogResponse
    {
        $zoneStorage = $this->zoneStorageRepository->findBySlug($request->slug());

        $zoneStorage->changeFamilyLog($request->familyLog());

        $this->zoneStorageRepository->changeFamilyLog($zoneStorage);

        return new ChangeZoneStorageFamilyLogResponse($zoneStorage);
    }
}
