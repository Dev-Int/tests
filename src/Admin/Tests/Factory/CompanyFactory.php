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

use Admin\Adapters\Gateway\ORM\Entity\Company;
use Admin\Tests\DataBuilder\CompanyDataBuilder;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Company>
 */
final class CompanyFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Company::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'name' => self::faker()->company(),
            'address' => self::faker()->streetAddress(),
            'postalCode' => self::faker()->postcode(),
            'city' => self::faker()->city(),
            'country' => self::faker()->country(),
            'phone' => '+33297000000',
            'email' => self::faker()->email(),
            'contact' => self::faker()->name(),
        ];
    }

    protected function initialize(): static
    {
        return $this->instantiateWith(
            /**
             * @param array{
             *     name: string,
             *     address: string,
             *     postalCode: string,
             *     city: string,
             *     country: string,
             *     phone: string,
             *     email: string,
             *     contact: string
             * } $attributes
             */
            static function (array $attributes): Company {
                \assert(\is_string($attributes['name']));
                \assert(\is_string($attributes['address']));
                \assert(\is_string($attributes['postalCode']));
                \assert(\is_string($attributes['city']));
                \assert(\is_string($attributes['country']));
                \assert(\is_string($attributes['phone']));
                \assert(\is_string($attributes['email']));
                \assert(\is_string($attributes['contact']));

                $companyDomain = (new CompanyDataBuilder())
                    ->create($attributes['name'])
                    ->withAddress($attributes['address'])
                    ->withPostalCode($attributes['postalCode'])
                    ->withTown($attributes['city'])
                    ->build()
                ;

                return Company::fromDomain($companyDomain);
            }
        );
    }
}
