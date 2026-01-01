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
use Admin\Adapters\Gateway\ORM\Entity\Supplier;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Supplier>
 */
final class SupplierFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Supplier::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'name' => self::faker()->company(),
            'uuid' => self::faker()->uuid(),
            'familyLog' => FamilyLogFactory::new(),
            'streetAddress' => self::faker()->streetAddress(),
            'postalCode' => self::faker()->regexify('[0-9]{5}'),
            'town' => self::faker()->city(),
            'phone' => '+33297000000',
            'cellphone' => '+33600000000',
            'email' => self::faker()->email(),
            'contact' => self::faker()->name(),
            'delayDelivery' => 3,
            'orderDays' => [1, 4],
        ];
    }

    protected function initialize(): static
    {
        return $this->instantiateWith(
            /**
             * @param array{name: string, uuid: string, familyLog: FamilyLog, streetAddress: string, postalCode: string, town: string, phone: string, cellphone: string, email: string, contact: string, delayDelivery: int, orderDays: array<int>} $attributes
             */
            static function (array $attributes): Supplier {
                \assert(\is_string($attributes['name']));
                \assert(\is_string($attributes['uuid']));
                \assert($attributes['familyLog'] instanceof FamilyLog);
                \assert(\is_string($attributes['streetAddress']));
                \assert(\is_string($attributes['postalCode']));
                \assert(\is_string($attributes['town']));
                \assert(\is_string($attributes['phone']));
                \assert(\is_string($attributes['cellphone']));
                \assert(\is_string($attributes['email']));
                \assert(\is_string($attributes['contact']));
                \assert(\is_int($attributes['delayDelivery']));
                \assert(\is_array($attributes['orderDays']));

                $familyLogDomain = $attributes['familyLog']->toDomain($attributes['familyLog']->parent());

                $supplierDomain = (new SupplierDataBuilder())
                    ->create($attributes['name'], $familyLogDomain)
                    ->withUuid($attributes['uuid'])
                    ->withAddress($attributes['streetAddress'])
                    ->withPostalCode($attributes['postalCode'])
                    ->withTown($attributes['town'])
                    ->withPhone($attributes['phone'])
                    ->withCellphone($attributes['cellphone'])
                    ->withEmail($attributes['email'])
                    ->withContact($attributes['contact'])
                    ->withDelayDelivery($attributes['delayDelivery'])
                    ->withOrderDays($attributes['orderDays'])
                    ->build()
                ;

                return (new Supplier())->fromDomain($supplierDomain, $attributes['familyLog']);
            }
        );
    }
}
