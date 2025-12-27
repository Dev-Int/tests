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

namespace Inventory\Tests\Factory;

use Inventory\Entities\VO\InventoryDate;
use Inventory\Entities\VO\InventoryStatus;
use Inventory\Tests\DataBuilder\InventoryDataBuilder;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\ResourceUuid;

final class InventoryFakerFactory
{
    public const string UUID_VALID = '852313f1-e2c6-4fea-a0f6-69e881091c0f';
    public const int AMOUNT_VALID = 1000;

    public function createDraft(): InventoryDataBuilder
    {
        return new InventoryDataBuilder(
            uuid: ResourceUuid::fromString(self::UUID_VALID),
            date: InventoryDate::fromDateTimeImmutable(ClockFactory::clock()->now()),
            zoneStorages: [],
            status: InventoryStatus::DRAFT
        );
    }

    public function createInProgress(): InventoryDataBuilder
    {
        return (new InventoryDataBuilder(
            uuid: ResourceUuid::fromString(self::UUID_VALID),
            date: InventoryDate::fromDateTimeImmutable(ClockFactory::clock()->now()),
            zoneStorages: [],
            status: InventoryStatus::IN_PROGRESS
        ))
            ->withAmount(1000)
        ;
    }

    public function createReviewed(): InventoryDataBuilder
    {
        return (new InventoryDataBuilder(
            uuid: ResourceUuid::fromString(self::UUID_VALID),
            date: InventoryDate::fromDateTimeImmutable(ClockFactory::clock()->now()),
            zoneStorages: [],
            status: InventoryStatus::REVIEW
        ))
            ->withAmount(self::AMOUNT_VALID)
        ;
    }

    public function createCompleted(): InventoryDataBuilder
    {
        return (new InventoryDataBuilder(
            uuid: ResourceUuid::fromString(self::UUID_VALID),
            date: InventoryDate::fromDateTimeImmutable(ClockFactory::clock()->now()),
            zoneStorages: [],
            status: InventoryStatus::COMPLETED
        ))
            ->withAmount(self::AMOUNT_VALID)
        ;
    }
}
