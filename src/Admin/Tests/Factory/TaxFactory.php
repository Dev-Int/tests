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

use Admin\Adapters\Gateway\ORM\Entity\Tax;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Tax>
 */
final class TaxFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Tax::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'name' => self::faker()->words(3, true),
            'rate' => self::faker()->randomFloat(2, 0, 30),
            'uuid' => self::faker()->uuid(),
        ];
    }

    protected function initialize(): static
    {
        return $this->instantiateWith(
            /**
             * @param array{name: string, rate: float, uuid: string} $attributes
             */
            static function (array $attributes): Tax {
                \assert(\is_string($attributes['name']));
                \assert(\is_float($attributes['rate']));
                \assert(\is_string($attributes['uuid']));

                $taxDomain = (new TaxDataBuilder())
                    ->create($attributes['name'], $attributes['rate'])
                    ->withUuid($attributes['uuid'])
                    ->build()
                ;

                return (new Tax())->fromDomain($taxDomain);
            }
        );
    }
}
