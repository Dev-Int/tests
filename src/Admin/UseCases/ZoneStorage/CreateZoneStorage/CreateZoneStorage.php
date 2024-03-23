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

namespace Admin\UseCases\ZoneStorage\CreateZoneStorage;

use Admin\Entities\Exception\ZoneStorageAlreadyExistsException;
use Admin\Entities\ZoneStorage\ZoneStorage;
use Admin\UseCases\Gateway\ZoneStorageRepository;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\NameField;

final readonly class CreateZoneStorage
{
    public function __construct(private ZoneStorageRepository $zoneStorageRepository)
    {
    }

    public function execute(CreateZoneStorageRequest $request): CreateZoneStorageResponse
    {
        $isExists = $this->zoneStorageRepository->exists($request->label());
        if ($isExists) {
            throw new ZoneStorageAlreadyExistsException($request->label());
        }

        $zoneStorage = ZoneStorage::create(
            ResourceUuid::generate(),
            NameField::fromString($request->label()),
            $request->familyLog()
        );

        $this->zoneStorageRepository->save($zoneStorage);

        return new CreateZoneStorageResponse($zoneStorage);
    }
}
