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

namespace Admin\UseCases\ZoneStorage\ChangeZoneStorageLabel;

use Admin\Entities\Exception\ZoneStorage\ZoneStorageAlreadyExists;
use Admin\Entities\Repository\ZoneStorageRepository;
use Shared\Entities\VO\NameField;

final readonly class ChangeZoneStorageLabel
{
    public function __construct(private ZoneStorageRepository $zoneStorageRepository)
    {
    }

    public function execute(ChangeZoneStorageLabelRequest $request): ChangeZoneStorageLabelResponse
    {
        $isExists = $this->zoneStorageRepository->exists($request->label());
        if ($isExists) {
            throw new ZoneStorageAlreadyExists($request->label());
        }
        $zoneStorage = $this->zoneStorageRepository->getBySlug($request->slug());
        $zoneStorage->changeLabel(NameField::fromString($request->label()));

        $this->zoneStorageRepository->changeLabel($zoneStorage);

        return new ChangeZoneStorageLabelResponse($zoneStorage);
    }
}
