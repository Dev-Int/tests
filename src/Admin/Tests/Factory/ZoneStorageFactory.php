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

namespace Admin\Tests\Factory;

use Admin\Adapters\Gateway\ORM\Entity\FamilyLog\FamilyLog;
use Admin\Adapters\Gateway\ORM\Entity\ZoneStorage;
use Admin\Tests\DataBuilder\ZoneStorageDataBuilder;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<ZoneStorage>
 */
final class ZoneStorageFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return ZoneStorage::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'label' => self::faker()->words(2, true),
            'uuid' => self::faker()->uuid(),
            'familyLog' => FamilyLogFactory::new(),
        ];
    }

    protected function initialize(): static
    {
        return $this->instantiateWith(
            /**
             * @param array{label: string, uuid: string, familyLog: FamilyLog} $attributes
             */
            static function (array $attributes): ZoneStorage {
                \assert(\is_string($attributes['label']));
                \assert(\is_string($attributes['uuid']));
                \assert($attributes['familyLog'] instanceof FamilyLog);

                $familyLogDomain = $attributes['familyLog']->toDomain($attributes['familyLog']->parent());

                $zoneStorageDomain = (new ZoneStorageDataBuilder())
                    ->create($attributes['label'], $familyLogDomain)
                    ->withUuid($attributes['uuid'])
                    ->build()
                ;

                return (new ZoneStorage())->fromDomain($zoneStorageDomain, $attributes['familyLog']);
            }
        );
    }
}
