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

use Admin\Adapters\Gateway\ORM\Entity\Employee;
use Admin\Tests\DataBuilder\EmployeeDataBuilder;
use Shared\Entities\ResourceUuid;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Employee>
 */
final class EmployeeFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Employee::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'firstName' => self::faker()->firstName(),
            'lastName' => self::faker()->lastName(),
            'email' => self::faker()->unique()->email(),
            'phone' => '0612345678',
            'position' => 'Developer',
            'department' => 'IT',
            'hiredAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTimeBetween('-2 years', 'now')),
        ];
    }

    protected function initialize(): static
    {
        return $this->instantiateWith(
            /**
             * @param array{
             *     firstName: string,
             *     lastName: string,
             *     email: string,
             *     phone: string,
             *     position: string,
             *     department: string,
             *     hiredAt: \DateTimeImmutable,
             *     userUuid?: string,
             *     disabledAt?: ?\DateTimeImmutable
             * } $attributes
             */
            static function (array $attributes) {
                \assert(\is_string($attributes['firstName']));
                \assert(\is_string($attributes['lastName']));
                \assert(\is_string($attributes['email']));
                \assert(\is_string($attributes['phone']));
                \assert(\is_string($attributes['position']));
                \assert(\is_string($attributes['department']));
                \assert($attributes['hiredAt'] instanceof \DateTimeImmutable);

                $builder = EmployeeDataBuilder::anEmployee()
                    ->withFirstName($attributes['firstName'])
                    ->withLastName($attributes['lastName'])
                    ->withEmail($attributes['email'])
                    ->withPhone($attributes['phone'])
                    ->withPosition($attributes['position'])
                    ->withDepartment($attributes['department'])
                    ->withHiredAt($attributes['hiredAt'])
                ;

                if (isset($attributes['userUuid']) && \is_string($attributes['userUuid'])) {
                    $builder->withUserUuid(ResourceUuid::fromString($attributes['userUuid']));
                }

                if (isset($attributes['disabledAt']) && $attributes['disabledAt'] instanceof \DateTimeImmutable) {
                    $builder->disabled();
                }

                return Employee::fromDomain($builder->build());
            }
        );
    }
}
