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

use Admin\Tests\Factory\ZoneStorageFactory;
use Inventory\Adapters\Gateway\ORM\Entity\Inventory;
use Inventory\Adapters\Gateway\ORM\Entity\InventoryStatus;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Inventory>
 */
final class InventoryFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Inventory::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'uuid' => self::faker()->uuid(),
            'date' => self::faker()->dateTime(),
            'zoneStorages' => [ZoneStorageFactory::new()],
            'status' => self::faker()->randomElement(InventoryStatus::ACTIVE_STATUSES),
            'amount' => self::faker()->numberBetween(100, 10000),
        ];
    }

    protected function initialize(): static
    {
        return $this->instantiateWith(
            /**
             * @param array{
             *     uuid: string,
             *     date: \DateTimeImmutable,
             *     zoneStorages: array<string>,
             *     status: string,
             *     amount: int
             * } $attributes
             */
            static function (array $attributes): Inventory {
                \assert(\is_string($attributes['uuid']));
                \assert($attributes['date'] instanceof \DateTimeImmutable);
                \assert(\is_array($attributes['zoneStorages']));
                \assert(\is_string($attributes['status']));
                \assert(\is_int($attributes['amount']));

                return new Inventory(
                    uuid: $attributes['uuid'],
                    date: $attributes['date'],
                    zoneStorages: $attributes['zoneStorages'],
                    status: InventoryStatus::from($attributes['status']),
                    amount: $attributes['amount'],
                    items: []
                );
            }
        );
    }
}
