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

namespace Auth\Tests\Adapters\Contracts\Services\CommandHandler\CreateUser;

use Auth\Adapters\ContractsHandler\Services\CommandHandler\CreateUser\CreateUserCommandHandler;
use Auth\Contracts\Exception\EmailAlreadyExists as ContractEmailAlreadyExists;
use Auth\Contracts\Services\CommandHandler\CreateUser\CreateUserCommand;
use Auth\Entities\Repository\UserRepository;
use Auth\Tests\Factory\UserFactory;
use Auth\UseCases\Gateway\PasswordHasherGateway;
use Auth\UseCases\User\CreateUser\CreateUser;
use Shared\Entities\ResourceUuid;
use Shared\Entities\Role;
use Shared\Entities\VO\EmailField;
use Shared\Tests\BaseFunctionalTestCase;

/**
 * @group functionalTest
 *
 * @covers \Auth\Adapters\ContractsHandler\Services\CommandHandler\CreateUser\CreateUserCommandHandler
 * @covers \Auth\Adapters\ContractsHandler\Services\CommandHandler\CreateUser\InternalCreateUserRequest
 */
final class CreateUserCommandHandlerTest extends BaseFunctionalTestCase
{
    private CreateUserCommandHandler $userCreator;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $container = self::getContainer();

        // Get real dependencies from container
        $userRepository = $container->get(UserRepository::class);
        \assert($userRepository instanceof UserRepository);
        $this->userRepository = $userRepository;

        $passwordHasher = $container->get(PasswordHasherGateway::class);
        \assert($passwordHasher instanceof PasswordHasherGateway);

        // Build the full chain: Adapter -> UseCase -> Real Repository + PasswordHasher
        $createUserUseCase = new CreateUser($this->userRepository, $passwordHasher);
        $this->userCreator = new CreateUserCommandHandler($createUserUseCase);
    }

    public function testCreateUserSuccessfullyPersistsInDatabase(): void
    {
        // Arrange
        $command = new CreateUserCommand(
            email: EmailField::fromString('newuser@example.com'),
            plainPassword: 'SecureP@ssw0rd!',
            roles: [Role::USER],
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
            email: EmailField::fromString('hashtest@example.com'),
            plainPassword: $plainPassword,
            roles: [Role::USER],
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
            email: EmailField::fromString('existing@example.com'),
            plainPassword: 'SecureP@ssw0rd!',
            roles: [Role::USER],
        );

        // Assert
        $this->expectException(ContractEmailAlreadyExists::class);
        $this->expectExceptionMessage(ContractEmailAlreadyExists::MESSAGE);

        // Act
        $this->userCreator->createUser($command);
    }

    public function testCreateUserAssignsRolesCorrectly(): void
    {
        // Arrange
        $command = new CreateUserCommand(
            email: EmailField::fromString('admin@example.com'),
            plainPassword: 'AdminP@ss!',
            roles: [Role::USER, Role::ADMIN],
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
