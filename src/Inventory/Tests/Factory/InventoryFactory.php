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
use Inventory\Entities\VO\InventoryStatus as InventoryStatusDomain;
use Shared\Entities\Clock\ClockFactory;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

use function PHPUnit\Framework\assertNull;

/**
 * @extends PersistentProxyObjectFactory<Inventory>
 */
final class InventoryFactory extends PersistentProxyObjectFactory
{
    private \DateTimeImmutable $now;

    public static function class(): string
    {
        return Inventory::class;
    }

    public function __construct()
    {
        parent::__construct();
        $this->now = ClockFactory::clock()->now();
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
            'status' => self::faker()->randomElement(InventoryStatusDomain::ACTIVE_STATUSES),
            'amount' => self::faker()->numberBetween(100, 10000),
            'createdAt' => $this->now,
            'updatedAt' => $this->now,
            'statusUpdatedAt' => $this->now,
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
             *     amount: int,
             *     createdAt: ?\DateTimeImmutable,
             *     updatedAt: ?\DateTimeImmutable,
             *     statusUpdatedAt: ?\DateTimeImmutable,
             * } $attributes
             */
            static function (array $attributes): Inventory {
                $now = ClockFactory::clock()->now();
                \assert(\is_string($attributes['uuid']));
                \assert($attributes['date'] instanceof \DateTimeImmutable);
                \assert(\is_array($attributes['zoneStorages']));
                \assert(\is_string($attributes['status']));
                \assert(\is_int($attributes['amount']));
                if (null === $attributes['createdAt']) {
                    $attributes['createdAt'] = $now;
                }
                \assert($attributes['createdAt'] instanceof \DateTimeImmutable);
                if (null === $attributes['updatedAt']) {
                    $attributes['updatedAt'] = $now;
                }
                \assert($attributes['updatedAt'] instanceof \DateTimeImmutable);
                if (null === $attributes['statusUpdatedAt']) {
                    assertNull($attributes['statusUpdatedAt']);
                } else {
                    \assert($attributes['statusUpdatedAt'] instanceof \DateTimeImmutable);
                }

                return new Inventory(
                    uuid: $attributes['uuid'],
                    date: $attributes['date'],
                    zoneStorages: $attributes['zoneStorages'],
                    status: InventoryStatus::from($attributes['status']),
                    amount: $attributes['amount'],
                    createdAt: $attributes['createdAt'],
                    updatedAt: $attributes['updatedAt'],
                    items: [],
                    statusUpdatedAt: $attributes['statusUpdatedAt']
                );
            }
        );
    }
}
