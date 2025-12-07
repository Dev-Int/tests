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
        ];
    }

    protected function initialize(): static
    {
        return $this->instantiateWith(
            /**
             * @param array{name: string} $attributes
             */
            static function (array $attributes): Company {
                \assert(\is_string($attributes['name']));

                $companyDomain = (new CompanyDataBuilder())
                    ->create($attributes['name'])
                    ->build()
                ;

                return Company::fromDomain($companyDomain);
            }
        );
    }
}
