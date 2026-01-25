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

namespace Auth\Tests\Adapters\Contracts\Services\Updater\User;

use Auth\Adapters\Contracts\Services\Updater\User\UserUpdater;
use Auth\Contracts\Exception\EmailAlreadyExists as ContractEmailAlreadyExists;
use Auth\Contracts\Services\Updater\User\UpdateUserCommand;
use Auth\Entities\Repository\UserRepository;
use Auth\Entities\Role;
use Auth\Tests\Factory\UserFactory;
use Auth\UseCases\User\UpdateUser\UpdateUser;
use Faker\Factory;
use Faker\Generator;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\EmailField;
use Shared\Tests\BaseFunctionalTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @group functionalTest
 *
 * @covers \Auth\Adapters\Contracts\Services\Updater\User\InternalUpdateUserRequest
 * @covers \Auth\Adapters\Contracts\Services\Updater\User\UserUpdater
 */
final class UserUpdaterTest extends BaseFunctionalTestCase
{
    private UserUpdater $userUpdater;
    private UserRepository $userRepository;
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $container = self::getContainer();

        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        $this->userRepository = $userRepository;

        $passwordHasher = $container->get(UserPasswordHasherInterface::class);
        \assert($passwordHasher instanceof UserPasswordHasherInterface);

        // Build the full chain: Adapter -> UseCase -> Real Repository + PasswordHasher
        $updateUserUseCase = new UpdateUser($this->userRepository, $passwordHasher);
        $this->userUpdater = new UserUpdater($updateUserUseCase);

        $this->faker = Factory::create();
    }

    public function testUpdateUserEmailPersistsInDatabase(): void
    {
        // Arrange
        $uuid = $this->faker->uuid();
        UserFactory::createOne([
            'uuid' => $uuid,
            'email' => 'original@example.com',
            'roles' => [Role::USER],
        ]);

        $command = new UpdateUserCommand(
            uuid: $uuid,
            email: EmailField::fromString('updated@example.com'),
        );

        // Act
        $result = $this->userUpdater->updateUser($command);

        // Assert
        $persistedUser = $this->userRepository->getByUuid(ResourceUuid::fromString($uuid));

        self::assertSame('updated@example.com', $result->email);
        self::assertSame('updated@example.com', $persistedUser->email()->toString());
    }

    public function testUpdateUserKeepsSameEmailWithoutError(): void
    {
        // Arrange
        $uuid = $this->faker->uuid();
        $sameEmail = EmailField::fromString('same@example.com');
        UserFactory::createOne([
            'uuid' => $uuid,
            'email' => $sameEmail->toString(),
            'roles' => [Role::USER],
        ]);

        $command = new UpdateUserCommand(
            uuid: $uuid,
            email: $sameEmail,
        );

        // Act
        $result = $this->userUpdater->updateUser($command);

        // Assert
        self::assertSame($sameEmail->toString(), $result->email);
    }

    public function testUpdateUserThrowsContractExceptionWhenEmailTaken(): void
    {
        // Arrange
        $uuid = $this->faker->uuid();
        UserFactory::createOne([
            'uuid' => $uuid,
            'email' => 'user1@example.com',
            'roles' => [Role::USER],
        ]);
        UserFactory::createOne([
            'email' => 'taken@example.com',
            'roles' => [Role::USER],
        ]);

        $command = new UpdateUserCommand(
            uuid: $uuid,
            email: EmailField::fromString('taken@example.com'),
        );

        // Assert
        $this->expectException(ContractEmailAlreadyExists::class);
        $this->expectExceptionMessage(ContractEmailAlreadyExists::MESSAGE);

        // Act
        $this->userUpdater->updateUser($command);
    }

    public function testUpdateUserRolesPersistsInDatabase(): void
    {
        // Arrange
        $uuid = $this->faker->uuid();
        UserFactory::createOne([
            'uuid' => $uuid,
            'email' => 'roletest@example.com',
            'roles' => [Role::USER],
        ]);

        $command = new UpdateUserCommand(
            uuid: $uuid,
            roles: ['ROLE_USER', 'ROLE_ADMIN'],
        );

        // Act
        $result = $this->userUpdater->updateUser($command);

        // Assert
        $persistedUser = $this->userRepository->getByUuid(ResourceUuid::fromString($uuid));
        $roles = array_map(
            static fn ($role) => $role->value,
            $persistedUser->roles()
        );

        self::assertContains('ROLE_USER', $result->roles);
        self::assertContains('ROLE_ADMIN', $result->roles);
        self::assertContains('ROLE_USER', $roles);
        self::assertContains('ROLE_ADMIN', $roles);
    }

    public function testUpdateUserPasswordHashesAndPersists(): void
    {
        // Arrange
        $uuid = $this->faker->uuid();
        $oldHash = '$2y$13$oldhashedpassword123456789012345678901234567890';
        UserFactory::createOne([
            'uuid' => $uuid,
            'email' => 'passtest@example.com',
            'password' => $oldHash,
            'roles' => [Role::USER],
        ]);

        $newPlainPassword = 'NewSecureP@ss!';
        $command = new UpdateUserCommand(
            uuid: $uuid,
            plainPassword: $newPlainPassword,
        );

        // Act
        $this->userUpdater->updateUser($command);

        // Assert
        $persistedUser = $this->userRepository->getByUuid(ResourceUuid::fromString($uuid));
        $newHashedPassword = $persistedUser->password()->toString();

        self::assertNotSame($oldHash, $newHashedPassword);
        self::assertNotSame($newPlainPassword, $newHashedPassword);
        self::assertStringStartsWith('$2y$', $newHashedPassword);
    }
}
