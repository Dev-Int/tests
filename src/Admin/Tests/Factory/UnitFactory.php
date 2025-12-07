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

use Admin\Adapters\Gateway\ORM\Entity\Unit;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Unit>
 */
final class UnitFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Unit::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'label' => self::faker()->words(2, true),
            'abbreviation' => self::faker()->lexify('???'),
            'uuid' => self::faker()->uuid(),
        ];
    }

    protected function initialize(): static
    {
        return $this->instantiateWith(
            /**
             * @param array{label: string, abbreviation: string, uuid: string} $attributes
             */
            static function (array $attributes): Unit {
                \assert(\is_string($attributes['label']));
                \assert(\is_string($attributes['abbreviation']));
                \assert(\is_string($attributes['uuid']));

                $unitDomain = (new UnitDataBuilder())
                    ->create($attributes['label'], $attributes['abbreviation'])
                    ->withUuid($attributes['uuid'])
                    ->build()
                ;

                return (new Unit())->fromDomain($unitDomain);
            }
        );
    }
}
