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

namespace Auth\Tests\Adapters\Contracts\Services\Creator\User;

use Auth\Adapters\Contracts\Services\Creator\User\UserCreator;
use Auth\Contracts\Exception\EmailAlreadyExists as ContractEmailAlreadyExists;
use Auth\Contracts\Services\Creator\User\CreateUserCommand;
use Auth\Entities\Repository\UserRepository;
use Auth\Tests\Factory\UserFactory;
use Auth\UseCases\User\CreateUser\CreateUser;
use Shared\Entities\ResourceUuid;
use Shared\Tests\BaseFunctionalTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @group functionalTest
 *
 * @covers \Auth\Adapters\Contracts\Services\Creator\User\InternalCreateUserRequest
 * @covers \Auth\Adapters\Contracts\Services\Creator\User\UserCreator
 */
final class UserCreatorTest extends BaseFunctionalTestCase
{
    private UserCreator $userCreator;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $container = self::getContainer();

        // Get real dependencies from container
        $userRepository = $container->get(UserRepository::class);
        \assert($userRepository instanceof UserRepository);
        $this->userRepository = $userRepository;

        $passwordHasher = $container->get(UserPasswordHasherInterface::class);
        \assert($passwordHasher instanceof UserPasswordHasherInterface);

        // Build the full chain: Adapter -> UseCase -> Real Repository + PasswordHasher
        $createUserUseCase = new CreateUser($this->userRepository, $passwordHasher);
        $this->userCreator = new UserCreator($createUserUseCase);
    }

    public function testCreateUserSuccessfullyPersistsInDatabase(): void
    {
        // Arrange
        $command = new CreateUserCommand(
            email: 'newuser@example.com',
            plainPassword: 'SecureP@ssw0rd!',
            roles: ['ROLE_USER'],
        );

        // Act
        $result = $this->userCreator->createUser($command);

        // Assert - User is persisted in DB
        $persistedUser = $this->userRepository->getByUuid(ResourceUuid::fromString($result->uuid));

        self::assertSame('newuser@example.com', $persistedUser->email()->toString());
        self::assertTrue($persistedUser->isActive());
    }

    public function testCreateUserHashesPasswordCorrectly(): void
    {
        // Arrange
        $plainPassword = 'MySecureP@ss123!';
        $command = new CreateUserCommand(
            email: 'hashtest@example.com',
            plainPassword: $plainPassword,
            roles: ['ROLE_USER'],
        );

        // Act
        $result = $this->userCreator->createUser($command);

        // Assert - Password is properly hashed (not stored as plain text)
        $persistedUser = $this->userRepository->getByUuid(ResourceUuid::fromString($result->uuid));
        $hashedPassword = $persistedUser->password()->toString();

        self::assertNotSame($plainPassword, $hashedPassword);
        self::assertStringStartsWith('$2y$', $hashedPassword); // bcrypt format
    }

    public function testCreateUserThrowsContractExceptionWhenEmailExists(): void
    {
        // Arrange - Create existing user in DB
        UserFactory::createOne(['email' => 'existing@example.com']);

        $command = new CreateUserCommand(
            email: 'existing@example.com',
            plainPassword: 'SecureP@ssw0rd!',
            roles: ['ROLE_USER'],
        );

        // Assert
        $this->expectException(ContractEmailAlreadyExists::class);
        $this->expectExceptionMessage('existing@example.com');

        // Act
        $this->userCreator->createUser($command);
    }

    public function testCreateUserAssignsRolesCorrectly(): void
    {
        // Arrange
        $command = new CreateUserCommand(
            email: 'admin@example.com',
            plainPassword: 'AdminP@ss!',
            roles: ['ROLE_USER', 'ROLE_ADMIN'],
        );

        // Act
        $result = $this->userCreator->createUser($command);

        // Assert
        $persistedUser = $this->userRepository->getByUuid(ResourceUuid::fromString($result->uuid));
        $roles = array_map(
            static fn ($role) => $role->value,
            $persistedUser->roles()
        );

        self::assertContains('ROLE_USER', $roles);
        self::assertContains('ROLE_ADMIN', $roles);
    }
}
