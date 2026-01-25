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

namespace Auth\Tests\Adapters\Contracts\Services\Disabler\User;

use Auth\Adapters\Contracts\Services\Disabler\User\UserDisabler;
use Auth\Contracts\Exception\UserAlreadyDisabled as ContractUserAlreadyDisabled;
use Auth\Contracts\Services\Disabler\User\DisableUserCommand;
use Auth\Entities\Repository\UserRepository;
use Auth\Entities\Role;
use Auth\Tests\Factory\UserFactory;
use Auth\UseCases\User\DisableUser\DisableUser;
use Faker\Factory;
use Faker\Generator;
use Shared\Entities\ResourceUuid;
use Shared\Tests\BaseFunctionalTestCase;

/**
 * @group functionalTest
 *
 * @covers \Auth\Adapters\Contracts\Services\Disabler\User\InternalDisableUserRequest
 * @covers \Auth\Adapters\Contracts\Services\Disabler\User\UserDisabler
 */
final class UserDisablerTest extends BaseFunctionalTestCase
{
    private UserDisabler $userDisabler;
    private UserRepository $userRepository;
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $container = self::getContainer();

        $userRepository = $container->get(UserRepository::class);
        \assert($userRepository instanceof UserRepository);
        $this->userRepository = $userRepository;

        // Build the full chain: Adapter -> UseCase -> Real Repository
        $disableUserUseCase = new DisableUser($this->userRepository);
        $this->userDisabler = new UserDisabler($disableUserUseCase);

        $this->faker = Factory::create();
    }

    public function testDisableUserPersistsInDatabase(): void
    {
        $uuid = $this->faker->uuid();
        UserFactory::createOne([
            'uuid' => $uuid,
            'email' => 'todisable@example.com',
            'roles' => [Role::USER],
            'disabledAt' => null,
        ]);

        $command = new DisableUserCommand(uuid: $uuid);

        // Act
        $result = $this->userDisabler->disableUser($command);

        // Assert
        $persistedUser = $this->userRepository->getByUuid(ResourceUuid::fromString($uuid));

        self::assertSame($uuid, $result->uuid);
        self::assertSame('todisable@example.com', $result->email);
        self::assertFalse($result->isActive);
        self::assertFalse($persistedUser->isActive());
    }

    public function testDisableUserThrowsContractExceptionWhenAlreadyDisabled(): void
    {
        // Arrange
        $uuid = $this->faker->uuid();
        UserFactory::createOne([
            'uuid' => $uuid,
            'email' => 'alreadydisabled@example.com',
            'roles' => [Role::USER],
            'disabledAt' => new \DateTimeImmutable(),
        ]);

        $command = new DisableUserCommand(uuid: $uuid);

        // Assert
        $this->expectException(ContractUserAlreadyDisabled::class);
        $this->expectExceptionMessage($uuid);

        // Act
        $this->userDisabler->disableUser($command);
    }

    public function testDisableUserReturnsCorrectResult(): void
    {
        // Arrange
        $uuid = $this->faker->uuid();
        UserFactory::createOne([
            'uuid' => $uuid,
            'email' => 'resulttest@example.com',
            'roles' => [Role::USER, Role::ADMIN],
            'disabledAt' => null,
        ]);

        $command = new DisableUserCommand(uuid: $uuid);

        // Act
        $result = $this->userDisabler->disableUser($command);

        // Assert
        self::assertSame($uuid, $result->uuid);
        self::assertSame('resulttest@example.com', $result->email);
        self::assertFalse($result->isActive);
    }
}
