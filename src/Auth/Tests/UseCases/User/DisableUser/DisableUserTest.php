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

namespace Auth\Tests\UseCases\User\DisableUser;

use Auth\Entities\Exception\UserAlreadyDisabled;
use Auth\Entities\Repository\UserRepository;
use Auth\Tests\DataBuilder\UserDataBuilder;
use Auth\UseCases\User\DisableUser\DisableUser;
use Auth\UseCases\User\DisableUser\DisableUserRequest;
use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Shared\Entities\ResourceUuid;

/**
 * @group unitTest
 *
 * @covers \Auth\UseCases\User\DisableUser\DisableUser
 * @covers \Auth\UseCases\User\DisableUser\DisableUserRequest
 */
final class DisableUserTest extends TestCase
{
    public function testDisableUserSucceeds(): void
    {
        // Arrange
        $faker = Factory::create();
        $userRepository = $this->createMock(UserRepository::class);
        $useCase = new DisableUser($userRepository);
        $request = $this->createMock(DisableUserRequest::class);

        $uuid = ResourceUuid::fromString($faker->uuid());
        $existingUser = UserDataBuilder::aUser()
            ->withUuid($uuid)
            ->withEmail('user@example.com')
            ->build()
        ;

        $request->expects(self::once())->method('uuid')->willReturn($uuid);

        $userRepository->expects(self::once())
            ->method('getByUuid')
            ->with($uuid)
            ->willReturn($existingUser)
        ;
        $userRepository->expects(self::once())->method('update')->with($existingUser);

        // Act
        $response = $useCase->execute($request);
        $user = $response->user;

        // Assert
        self::assertFalse($user->isActive());
        self::assertNotNull($user->disabledAt());
    }

    public function testDisableUserThrowsExceptionWhenAlreadyDisabled(): void
    {
        // Arrange
        $faker = Factory::create();
        $userRepository = $this->createMock(UserRepository::class);
        $useCase = new DisableUser($userRepository);
        $request = $this->createMock(DisableUserRequest::class);

        $uuid = ResourceUuid::fromString($faker->uuid());
        $disabledUser = UserDataBuilder::aUser()
            ->withUuid($uuid)
            ->withDisabledAt(new \DateTimeImmutable())
            ->build()
        ;

        $request->expects(self::once())->method('uuid')->willReturn($uuid);

        $userRepository->expects(self::once())
            ->method('getByUuid')
            ->willReturn($disabledUser)
        ;
        $userRepository->expects(self::never())->method('update');

        // Act & Assert
        $this->expectException(UserAlreadyDisabled::class);
        $useCase->execute($request);
    }
}
