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

namespace Auth\Tests\UseCases\User\UpdateUser;

use Auth\Entities\Repository\UserRepository;
use Auth\Entities\VO\HashedPassword;
use Auth\Tests\DataBuilder\UserDataBuilder;
use Auth\UseCases\Gateway\PasswordHasherGateway;
use Auth\UseCases\User\UpdateUser\UpdateUser;
use Auth\UseCases\User\UpdateUser\UpdateUserRequest;
use PHPUnit\Framework\TestCase;
use Shared\Entities\ResourceUuid;
use Shared\Entities\Role;

/**
 * @group unitTest
 *
 * @covers \Auth\UseCases\User\UpdateUser\UpdateUser
 * @covers \Auth\UseCases\User\UpdateUser\UpdateUserRequest
 */
final class UpdateUserTest extends TestCase
{
    public function testUpdateUserPasswordSucceeds(): void
    {
        // Arrange
        $userRepository = $this->createMock(UserRepository::class);
        $passwordHasher = $this->createMock(PasswordHasherGateway::class);
        $useCase = new UpdateUser($userRepository, $passwordHasher);
        $request = $this->createMock(UpdateUserRequest::class);

        $uuid = ResourceUuid::fromString('550e8400-e29b-41d4-a716-446655440000');
        $existingUser = UserDataBuilder::aUser()
            ->withUuid($uuid)
            ->withEmail('user@example.com')
            ->build()
        ;

        $request->expects(self::once())->method('uuid')->willReturn($uuid);
        $request->expects(self::exactly(2))->method('plainPassword')->willReturn('NewPassword123');
        $request->expects(self::once())->method('roles')->willReturn(null);

        $userRepository->expects(self::once())
            ->method('getByUuid')
            ->willReturn($existingUser)
        ;
        $userRepository->expects(self::once())->method('update');

        $passwordHasher->expects(self::once())
            ->method('hashPassword')
            ->with('NewPassword123')
            ->willReturn(HashedPassword::fromHash('$2y$13$newhash'))
        ;

        // Act
        $response = $useCase->execute($request);
        $user = $response->user;

        // Assert
        self::assertSame('$2y$13$newhash', $user->password()->toString());
    }

    public function testUpdateUserRolesSucceeds(): void
    {
        // Arrange
        $userRepository = $this->createMock(UserRepository::class);
        $passwordHasher = $this->createMock(PasswordHasherGateway::class);
        $useCase = new UpdateUser($userRepository, $passwordHasher);
        $request = $this->createMock(UpdateUserRequest::class);

        $uuid = ResourceUuid::fromString('550e8400-e29b-41d4-a716-446655440000');
        $existingUser = UserDataBuilder::aUser()
            ->withUuid($uuid)
            ->withRoles([Role::USER])
            ->build()
        ;

        $request->expects(self::once())->method('uuid')->willReturn($uuid);
        $request->expects(self::once())->method('plainPassword')->willReturn(null);
        $request->expects(self::exactly(2))->method('roles')->willReturn([Role::ADMIN, Role::INVENTORY_MANAGER]);

        $userRepository->expects(self::once())
            ->method('getByUuid')
            ->willReturn($existingUser)
        ;
        $userRepository->expects(self::once())->method('update');

        // Act
        $response = $useCase->execute($request);
        $user = $response->user;

        // Assert
        self::assertTrue($user->hasRole(Role::ADMIN));
        self::assertTrue($user->hasRole(Role::INVENTORY_MANAGER));
        self::assertTrue($user->hasRole(Role::USER)); // Always present
    }
}
