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

namespace Auth\Tests\Adapters\Gateway\ORM\Entity;

use Auth\Adapters\Gateway\ORM\Entity\User as UserOrm;
use Auth\Entities\User as UserDomain;
use Auth\Entities\VO\HashedPassword;
use PHPUnit\Framework\TestCase;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\Clock\FrozenClock;
use Shared\Entities\ResourceUuid;
use Shared\Entities\Role;
use Shared\Entities\VO\EmailField;

/**
 * @group unitTest
 *
 * @covers \Auth\Adapters\Gateway\ORM\Entity\User
 */
final class UserTest extends TestCase
{
    protected function setUp(): void
    {
        ClockFactory::initialize(new FrozenClock(new \DateTimeImmutable('2026-01-15 10:00:00')));
    }

    public function testFromDomainCreatesOrmUser(): void
    {
        // Arrange
        $uuid = ResourceUuid::fromString('550e8400-e29b-41d4-a716-446655440000');
        $email = EmailField::fromString('user@example.com');
        $password = HashedPassword::fromHash('$2y$13$hashedpassword');
        $domainUser = UserDomain::create($uuid, $email, $password, [Role::ADMIN]);

        // Act
        $ormUser = UserOrm::fromDomain($domainUser);

        // Assert
        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $ormUser->uuid());
        self::assertSame('user@example.com', $ormUser->getUserIdentifier());
        self::assertSame('$2y$13$hashedpassword', $ormUser->getPassword());
        self::assertContains('ROLE_USER', $ormUser->getRoles());
        self::assertContains('ROLE_ADMIN', $ormUser->getRoles());
    }

    public function testToDomainCreatesDomainUser(): void
    {
        // Arrange
        $uuid = ResourceUuid::fromString('550e8400-e29b-41d4-a716-446655440000');
        $email = EmailField::fromString('user@example.com');
        $password = HashedPassword::fromHash('$2y$13$hashedpassword');
        $domainUser = UserDomain::create($uuid, $email, $password, [Role::ADMIN]);
        $ormUser = UserOrm::fromDomain($domainUser);

        // Act
        $reconstitutedUser = $ormUser->toDomain();

        // Assert
        self::assertSame($uuid->toString(), $reconstitutedUser->uuid()->toString());
        self::assertSame('user@example.com', $reconstitutedUser->email()->toString());
        self::assertSame('$2y$13$hashedpassword', $reconstitutedUser->password()->toString());
        self::assertTrue($reconstitutedUser->hasRole(Role::USER));
        self::assertTrue($reconstitutedUser->hasRole(Role::ADMIN));
    }

    public function testGetUserIdentifierReturnsEmail(): void
    {
        // Arrange
        $domainUser = UserDomain::create(
            ResourceUuid::generate(),
            EmailField::fromString('test@domain.com'),
            HashedPassword::fromHash('$2y$13$hashedpassword'),
        );
        $ormUser = UserOrm::fromDomain($domainUser);

        // Act & Assert
        self::assertSame('test@domain.com', $ormUser->getUserIdentifier());
    }

    public function testGetRolesReturnsStringArray(): void
    {
        // Arrange
        $domainUser = UserDomain::create(
            ResourceUuid::generate(),
            EmailField::fromString('admin@example.com'),
            HashedPassword::fromHash('$2y$13$hashedpassword'),
            [Role::ADMIN, Role::INVENTORY_MANAGER],
        );
        $ormUser = UserOrm::fromDomain($domainUser);

        // Act
        $roles = $ormUser->getRoles();

        // Assert
        self::assertContainsOnly('string', $roles);
        self::assertContains('ROLE_USER', $roles);
        self::assertContains('ROLE_ADMIN', $roles);
        self::assertContains('ROLE_INVENTORY_MANAGER', $roles);
    }

    public function testUpdateFromDomainModifiesOrmUser(): void
    {
        // Arrange
        $uuid = ResourceUuid::fromString('550e8400-e29b-41d4-a716-446655440000');
        $originalUser = UserDomain::create(
            $uuid,
            EmailField::fromString('original@example.com'),
            HashedPassword::fromHash('$2y$13$hashedpassword'),
        );
        $ormUser = UserOrm::fromDomain($originalUser);

        // Create updated domain user
        $updatedUser = UserDomain::reconstitute(
            $uuid,
            EmailField::fromString('updated@example.com'),
            HashedPassword::fromHash('$2y$13$newhashedpassword'),
            [Role::ADMIN],
            $originalUser->createdAt(),
            new \DateTimeImmutable('2026-01-15 12:00:00'),
        );

        // Act
        $ormUser->updateFromDomain($updatedUser);

        // Assert
        self::assertSame('updated@example.com', $ormUser->getUserIdentifier());
        self::assertSame('$2y$13$newhashedpassword', $ormUser->getPassword());
        self::assertContains('ROLE_ADMIN', $ormUser->getRoles());
    }

    public function testEraseCredentialsDoesNothing(): void
    {
        // Arrange
        $domainUser = UserDomain::create(
            ResourceUuid::generate(),
            EmailField::fromString('user@example.com'),
            HashedPassword::fromHash('$2y$13$hashedpassword'),
        );
        $ormUser = UserOrm::fromDomain($domainUser);
        $passwordBefore = $ormUser->getPassword();

        // Act
        $ormUser->eraseCredentials();

        // Assert - password should still be there (we don't store plaintext)
        self::assertSame($passwordBefore, $ormUser->getPassword());
    }
}
