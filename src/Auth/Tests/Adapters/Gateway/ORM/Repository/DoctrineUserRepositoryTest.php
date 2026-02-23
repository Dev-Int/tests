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
use Auth\Entities\VO\HashedPassword;
use Auth\Tests\DataBuilder\UserDataBuilder;
use Auth\Tests\Factory\UserFactory;
use Faker\Factory;
use Faker\Generator;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\Clock\FrozenClock;
use Shared\Entities\ResourceUuid;
use Shared\Entities\Role;
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
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var DoctrineUserRepository $repository */
        $repository = self::getContainer()->get(DoctrineUserRepository::class);
        $this->repository = $repository;

        $this->faker = Factory::create();
    }

    public function testGetByUuidReturnsUser(): void
    {
        // Arrange
        $uuid = $this->faker->uuid();
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

    public function testEmailExistsReturnsTrueForDisabledUser(): void
    {
        // Arrange — un user désactivé avec cet email
        $uuid = $this->faker->uuid();
        UserFactory::createOne([
            'uuid' => $uuid,
            'email' => 'disabled@example.com',
            'disabledAt' => new \DateTimeImmutable('2026-01-01 10:00:00'),
        ]);

        // Act & Assert — l'email est toujours bloqué même si le user est désactivé
        self::assertTrue($this->repository->emailExists(EmailField::fromString('disabled@example.com')));
    }

    public function testCreatePersistsUser(): void
    {
        // Arrange
        $uuid = $this->faker->uuid();
        $userDomain = UserDataBuilder::aUser()
            ->withUuid(ResourceUuid::fromString($uuid))
            ->withEmail('new@example.com')
            ->withPassword('$2y$13$hashedpassword')
            ->withRoles([Role::USER])
            ->build()
        ;

        // Act
        $this->repository->create($userDomain);

        // Assert
        $found = $this->repository->getByUuid(ResourceUuid::fromString($uuid));
        self::assertSame('new@example.com', $found->email()->toString());
    }

    public function testUpdateModifiesUser(): void
    {
        // Arrange
        $uuid = $this->faker->uuid();
        $oldHash = '$2y$13$oldhashpassword12345678901234567890';
        $newHash = '$2y$13$newhashpassword12345678901234567890';
        UserFactory::createOne([
            'uuid' => $uuid,
            'email' => 'original@example.com',
            'password' => $oldHash,
            'roles' => [Role::USER],
        ]);

        $userDomain = $this->repository->getByUuid(ResourceUuid::fromString($uuid));

        // Act
        $userDomain->changePassword(HashedPassword::fromHash($newHash));
        $this->repository->update($userDomain);

        // Assert
        $found = $this->repository->getByUuid(ResourceUuid::fromString($uuid));
        self::assertSame($newHash, $found->password()->toString());
    }

    public function testDisablePersistsDisabledAt(): void
    {
        // Arrange
        $disableTime = new \DateTimeImmutable('2026-01-17 14:00:00');
        ClockFactory::initialize(new FrozenClock($disableTime));

        $uuid = $this->faker->uuid();
        UserFactory::createOne([
            'uuid' => $uuid,
            'email' => 'todisable@example.com',
        ]);

        $userDomain = $this->repository->getByUuid(ResourceUuid::fromString($uuid));
        self::assertTrue($userDomain->isActive());

        // Act
        $userDomain->disable();
        $this->repository->update($userDomain);

        // Assert
        $found = $this->repository->getByUuid(ResourceUuid::fromString($uuid));
        self::assertFalse($found->isActive());
        self::assertEquals($disableTime, $found->disabledAt());
    }
}
