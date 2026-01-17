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

namespace Auth\Tests\Factory;

use Auth\Adapters\Gateway\ORM\Entity\User;
use Auth\Entities\Role;
use Auth\Tests\DataBuilder\UserDataBuilder;
use Shared\Entities\ResourceUuid;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<User>
 */
final class UserFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return User::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'email' => self::faker()->unique()->safeEmail(),
            'password' => '$2y$13$' . self::faker()->sha256(), // Valid bcrypt-like hash
            'roles' => [Role::USER],
            'uuid' => self::faker()->uuid(),
            'disabledAt' => null,
        ];
    }

    protected function initialize(): static
    {
        return $this->instantiateWith(
            static function (array $attributes): User {
                \assert(\is_string($attributes['email']));
                \assert(\is_string($attributes['password']));
                \assert(\is_array($attributes['roles']));
                \assert(\is_string($attributes['uuid']));
                \assert($attributes['disabledAt'] === null || $attributes['disabledAt'] instanceof \DateTimeImmutable);

                $userDomain = UserDataBuilder::aUser()
                    ->withUuid(ResourceUuid::fromString($attributes['uuid']))
                    ->withEmail($attributes['email'])
                    ->withPassword($attributes['password'])
                    ->withRoles($attributes['roles'])
                    ->withDisabledAt($attributes['disabledAt'])
                    ->build()
                ;

                return User::fromDomain($userDomain);
            }
        );
    }
}
