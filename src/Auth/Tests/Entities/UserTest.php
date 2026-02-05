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

namespace Auth\Tests\Entities;

use Auth\Entities\Exception\UserAlreadyDisabled;
use Auth\Entities\User;
use Auth\Entities\VO\HashedPassword;
use Auth\Tests\DataBuilder\UserDataBuilder;
use PHPUnit\Framework\TestCase;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\Clock\FrozenClock;
use Shared\Entities\ResourceUuid;
use Shared\Entities\Role;
use Shared\Entities\VO\EmailField;

/**
 * @group unitTest
 *
 * @covers \Auth\Entities\User
 */
final class UserTest extends TestCase
{
    protected function setUp(): void
    {
        ClockFactory::initialize(new FrozenClock(new \DateTimeImmutable('2025-01-15 10:00:00')));
    }

    public function testCreateUserWithDefaultRole(): void
    {
        // Arrange
        $uuid = ResourceUuid::generate();
        $email = EmailField::fromString('test@example.com');
        $password = HashedPassword::fromHash('$2y$13$hashedpassword');

        // Act
        $user = User::create($uuid, $email, $password);

        // Assert
        self::assertSame($uuid->toString(), $user->uuid()->toString());
        self::assertSame('test@example.com', $user->email()->toString());
        self::assertSame('$2y$13$hashedpassword', $user->password()->toString());
        self::assertContains(Role::USER, $user->roles());
    }

    public function testCreateUserWithSpecificRoles(): void
    {
        // Arrange
        $uuid = ResourceUuid::generate();
        $email = EmailField::fromString('admin@example.com');
        $password = HashedPassword::fromHash('$2y$13$hashedpassword');
        $roles = [Role::ADMIN];

        // Act
        $user = User::create($uuid, $email, $password, $roles);

        // Assert
        self::assertContains(Role::ADMIN, $user->roles());
        self::assertContains(Role::USER, $user->roles(), 'ROLE_USER always added');
    }

    public function testRolesAreNormalizedToUniqueValues(): void
    {
        // Arrange
        $uuid = ResourceUuid::generate();
        $email = EmailField::fromString('test@example.com');
        $password = HashedPassword::fromHash('$2y$13$hashedpassword');
        $roles = [Role::USER, Role::USER, Role::ADMIN];

        // Act
        $user = User::create($uuid, $email, $password, $roles);

        // Assert
        self::assertCount(2, $user->roles(), 'Roles should be unique');
        self::assertContains(Role::USER, $user->roles());
        self::assertContains(Role::ADMIN, $user->roles());
    }

    public function testReconstituteUser(): void
    {
        // Arrange
        $uuid = ResourceUuid::generate();
        $email = EmailField::fromString('user@example.com');
        $password = HashedPassword::fromHash('$2y$13$hashedpassword');
        $roles = [Role::USER, Role::ADMIN];
        $createdAt = new \DateTimeImmutable('2025-01-01 09:00:00');
        $updatedAt = new \DateTimeImmutable('2025-01-10 14:30:00');

        // Act
        $user = User::reconstitute($uuid, $email, $password, $roles, $createdAt, $updatedAt);

        // Assert
        self::assertSame($uuid->toString(), $user->uuid()->toString());
        self::assertEquals($createdAt, $user->createdAt());
        self::assertEquals($updatedAt, $user->updatedAt());
    }

    public function testReconstituteWithEmptyRolesAddsDefaultRole(): void
    {
        // Arrange
        $uuid = ResourceUuid::generate();
        $email = EmailField::fromString('user@example.com');
        $password = HashedPassword::fromHash('$2y$13$hashedpassword');
        $roles = []; // Empty roles from DB
        $createdAt = new \DateTimeImmutable('2025-01-01 09:00:00');
        $updatedAt = new \DateTimeImmutable('2025-01-10 14:30:00');

        // Act
        $user = User::reconstitute($uuid, $email, $password, $roles, $createdAt, $updatedAt);

        // Assert
        self::assertContains(Role::USER, $user->roles(), 'ROLE_USER should be added automatically.');
        self::assertCount(1, $user->roles());
    }

    public function testHasRoleReturnsTrueWhenUserHasRole(): void
    {
        // Arrange
        $user = UserDataBuilder::aUser()->asAdmin()->build();

        // Act & Assert
        self::assertTrue($user->hasRole(Role::ADMIN));
        self::assertTrue($user->hasRole(Role::USER));
    }

    public function testHasRoleReturnsFalseWhenUserDoesNotHaveRole(): void
    {
        // Arrange
        $user = UserDataBuilder::aUser()->build();

        // Act & Assert
        self::assertFalse($user->hasRole(Role::ADMIN));
    }

    public function testIsAdminReturnsTrueForAdmin(): void
    {
        // Arrange
        $user = UserDataBuilder::aUser()->asAdmin()->build();

        // Act & Assert
        self::assertTrue($user->isAdmin());
    }

    public function testIsAdminReturnsFalseForRegularUser(): void
    {
        // Arrange
        $user = UserDataBuilder::aUser()->build();

        // Act & Assert
        self::assertFalse($user->isAdmin());
    }

    public function testChangeEmailUpdatesEmailAndUpdatedAt(): void
    {
        // Arrange
        $initialTime = new \DateTimeImmutable('2025-01-01 10:00:00');
        $updateTime = new \DateTimeImmutable('2025-01-15 14:30:00');

        ClockFactory::initialize(new FrozenClock($initialTime));
        $user = UserDataBuilder::aUser()
            ->withEmail('old@example.com')
            ->withUpdatedAt($initialTime)
            ->build()
        ;

        ClockFactory::initialize(new FrozenClock($updateTime));
        $newEmail = EmailField::fromString('new@example.com');

        // Act
        $user->changeEmail($newEmail);

        // Assert
        self::assertSame('new@example.com', $user->email()->toString());
        self::assertEquals($updateTime, $user->updatedAt());
    }

    public function testChangePasswordUpdatesPasswordAndUpdatedAt(): void
    {
        // Arrange
        $initialTime = new \DateTimeImmutable('2025-01-01 10:00:00');
        $updateTime = new \DateTimeImmutable('2025-01-15 14:30:00');

        ClockFactory::initialize(new FrozenClock($initialTime));
        $user = UserDataBuilder::aUser()
            ->withPassword('$2y$13$oldhash')
            ->withUpdatedAt($initialTime)
            ->build()
        ;

        ClockFactory::initialize(new FrozenClock($updateTime));
        $newPassword = HashedPassword::fromHash('$2y$13$newhash');

        // Act
        $user->changePassword($newPassword);

        // Assert
        self::assertSame('$2y$13$newhash', $user->password()->toString());
        self::assertEquals($updateTime, $user->updatedAt());
    }

    public function testUpdateRolesNormalizesAndUpdatesTimestamp(): void
    {
        // Arrange
        $initialTime = new \DateTimeImmutable('2025-01-01 10:00:00');
        $updateTime = new \DateTimeImmutable('2025-01-15 14:30:00');

        ClockFactory::initialize(new FrozenClock($initialTime));
        $user = UserDataBuilder::aUser()
            ->withRoles([Role::USER])
            ->withUpdatedAt($initialTime)
            ->build()
        ;

        ClockFactory::initialize(new FrozenClock($updateTime));

        // Act
        $user->updateRoles([Role::ADMIN, Role::INVENTORY_MANAGER]);

        // Assert
        self::assertContains(Role::ADMIN, $user->roles());
        self::assertContains(Role::INVENTORY_MANAGER, $user->roles());
        self::assertContains(Role::USER, $user->roles(), 'Always present');
        self::assertEquals($updateTime, $user->updatedAt());
    }

    public function testIsActiveReturnsTrueWhenNotDisabled(): void
    {
        // Arrange
        $user = UserDataBuilder::aUser()->build();

        // Act & Assert
        self::assertTrue($user->isActive());
        self::assertNull($user->disabledAt());
    }

    public function testIsActiveReturnsFalseWhenDisabled(): void
    {
        // Arrange
        $disabledAt = new \DateTimeImmutable('2025-01-10 12:00:00');
        $user = UserDataBuilder::aUser()
            ->withDisabledAt($disabledAt)
            ->build()
        ;

        // Act & Assert
        self::assertFalse($user->isActive());
        self::assertEquals($disabledAt, $user->disabledAt());
    }

    public function testDisableUserSetsDisabledAt(): void
    {
        // Arrange
        $disableTime = new \DateTimeImmutable('2025-01-15 14:30:00');
        ClockFactory::initialize(new FrozenClock($disableTime));

        $user = UserDataBuilder::aUser()->build();
        self::assertTrue($user->isActive());

        // Act
        $user->disable();

        // Assert
        self::assertFalse($user->isActive());
        self::assertEquals($disableTime, $user->disabledAt());
        self::assertEquals($disableTime, $user->updatedAt());
    }

    public function testDisableAlreadyDisabledUserThrows(): void
    {
        // Arrange
        $user = UserDataBuilder::aUser()
            ->withDisabledAt(new \DateTimeImmutable('2025-01-10 12:00:00'))
            ->build()
        ;

        // Assert
        $this->expectException(UserAlreadyDisabled::class);

        // Act
        $user->disable();
    }
}
