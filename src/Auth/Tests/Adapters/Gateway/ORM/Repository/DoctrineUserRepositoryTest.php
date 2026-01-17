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

namespace Auth\Tests\Adapters\Gateway\ORM\Repository;

use Auth\Adapters\Gateway\ORM\Repository\DoctrineUserRepository;
use Auth\Entities\Exception\UserNotFoundByEmail;
use Auth\Entities\Exception\UserNotFoundById;
use Auth\Entities\Role;
use Auth\Tests\DataBuilder\UserDataBuilder;
use Auth\Tests\Factory\UserFactory;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\Clock\FrozenClock;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\EmailField;
use Shared\Tests\BaseFunctionalTestCase;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 *
 * @covers \Auth\Adapters\Gateway\ORM\Repository\DoctrineUserRepository
 */
final class DoctrineUserRepositoryTest extends BaseFunctionalTestCase
{
    use Factories;

    private DoctrineUserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var DoctrineUserRepository $repository */
        $repository = self::getContainer()->get(DoctrineUserRepository::class);
        $this->repository = $repository;
    }

    public function testGetByUuidReturnsUser(): void
    {
        // Arrange
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        UserFactory::createOne([
            'uuid' => $uuid,
            'email' => 'test@example.com',
            'password' => '$2y$13$hashedpassword',
            'roles' => [Role::USER],
        ]);

        // Act
        $user = $this->repository->getByUuid(ResourceUuid::fromString($uuid));

        // Assert
        self::assertSame($uuid, $user->uuid()->toString());
        self::assertSame('test@example.com', $user->email()->toString());
        self::assertTrue($user->hasRole(Role::USER));
    }

    public function testGetByUuidThrowsWhenNotFound(): void
    {
        // Arrange
        $nonExistentUuid = ResourceUuid::fromString('00000000-0000-0000-0000-000000000000');

        // Assert
        $this->expectException(UserNotFoundById::class);

        // Act
        $this->repository->getByUuid($nonExistentUuid);
    }

    public function testGetByEmailReturnsUser(): void
    {
        // Arrange
        UserFactory::createOne([
            'email' => 'findme@example.com',
            'password' => '$2y$13$hashedpassword',
            'roles' => [Role::USER, Role::ADMIN],
        ]);

        // Act
        $user = $this->repository->getByEmail(EmailField::fromString('findme@example.com'));

        // Assert
        self::assertSame('findme@example.com', $user->email()->toString());
        self::assertTrue($user->hasRole(Role::ADMIN));
    }

    public function testGetByEmailThrowsWhenNotFound(): void
    {
        // Assert
        $this->expectException(UserNotFoundByEmail::class);

        // Act
        $this->repository->getByEmail(EmailField::fromString('notfound@example.com'));
    }

    public function testEmailExistsReturnsTrueWhenExists(): void
    {
        // Arrange
        UserFactory::createOne([
            'email' => 'exists@example.com',
        ]);

        // Act & Assert
        self::assertTrue($this->repository->emailExists(EmailField::fromString('exists@example.com')));
    }

    public function testEmailExistsReturnsFalseWhenNotExists(): void
    {
        // Act & Assert
        self::assertFalse($this->repository->emailExists(EmailField::fromString('notexists@example.com')));
    }

    public function testCreatePersistsUser(): void
    {
        // Arrange
        $userDomain = UserDataBuilder::aUser()
            ->withUuid(ResourceUuid::fromString('11111111-1111-1111-1111-111111111111'))
            ->withEmail('new@example.com')
            ->withPassword('$2y$13$hashedpassword')
            ->withRoles([Role::USER])
            ->build()
        ;

        // Act
        $this->repository->create($userDomain);

        // Assert
        $found = $this->repository->getByUuid(ResourceUuid::fromString('11111111-1111-1111-1111-111111111111'));
        self::assertSame('new@example.com', $found->email()->toString());
    }

    public function testUpdateModifiesUser(): void
    {
        // Arrange
        $uuid = '22222222-2222-2222-2222-222222222222';
        UserFactory::createOne([
            'uuid' => $uuid,
            'email' => 'original@example.com',
            'password' => '$2y$13$hashedpassword',
            'roles' => [Role::USER],
        ]);

        // Get, modify, and update
        $userDomain = $this->repository->getByUuid(ResourceUuid::fromString($uuid));
        $userDomain->changeEmail(EmailField::fromString('updated@example.com'));

        // Act
        $this->repository->update($userDomain);

        // Assert
        $found = $this->repository->getByUuid(ResourceUuid::fromString($uuid));
        self::assertSame('updated@example.com', $found->email()->toString());
    }

    public function testDisablePersistsDisabledAt(): void
    {
        // Arrange
        $disableTime = new \DateTimeImmutable('2026-01-17 14:00:00');
        ClockFactory::initialize(new FrozenClock($disableTime));

        $uuid = '33333333-3333-3333-3333-333333333333';
        UserFactory::createOne([
            'uuid' => $uuid,
            'email' => 'todisable@example.com',
        ]);

        $userDomain = $this->repository->getByUuid(ResourceUuid::fromString($uuid));
        self::assertTrue($userDomain->isActive());

        // Disable the user in domain
        $userDomain->disable();

        // Act - persist the change
        $this->repository->disable($userDomain);

        // Assert - user still exists but is disabled
        $found = $this->repository->getByUuid(ResourceUuid::fromString($uuid));
        self::assertFalse($found->isActive());
        self::assertEquals($disableTime, $found->disabledAt());
    }
}
