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

namespace Auth\Tests\UseCases\User\CreateUser;

use Auth\Entities\Exception\EmailAlreadyExists;
use Auth\Entities\Repository\UserRepository;
use Auth\Entities\Role;
use Auth\UseCases\User\CreateUser\CreateUser;
use Auth\UseCases\User\CreateUser\CreateUserRequest;
use PHPUnit\Framework\TestCase;
use Shared\Entities\VO\EmailField;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @group unitTest
 *
 * @covers \Auth\UseCases\User\CreateUser\CreateUser
 * @covers \Auth\UseCases\User\CreateUser\CreateUserRequest
 */
final class CreateUserTest extends TestCase
{
    public function testCreateUserSucceeds(): void
    {
        // Arrange
        $userRepository = $this->createMock(UserRepository::class);
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $useCase = new CreateUser($userRepository, $passwordHasher);
        $request = $this->createMock(CreateUserRequest::class);

        $request->expects(self::once())->method('email')->willReturn(EmailField::fromString('admin@example.com'));
        $request->expects(self::once())->method('plainPassword')->willReturn('SecureP@ss123');
        $request->expects(self::once())->method('roles')->willReturn([Role::ADMIN]);

        $userRepository->expects(self::once())
            ->method('emailExists')
            ->willReturn(false)
        ;
        $userRepository->expects(self::once())->method('create');

        $passwordHasher->expects(self::once())
            ->method('hashPassword')
            ->willReturn('$2y$13$hashedpassword')
        ;

        // Act
        $response = $useCase->execute($request);
        $user = $response->user;

        // Assert
        self::assertSame('admin@example.com', $user->email()->toString());
        self::assertTrue($user->hasRole(Role::ADMIN));
        self::assertTrue($user->hasRole(Role::USER));
        self::assertTrue($user->isActive());
    }

    public function testCreateUserThrowsExceptionWhenEmailAlreadyExists(): void
    {
        // Arrange
        $userRepository = $this->createMock(UserRepository::class);
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $useCase = new CreateUser($userRepository, $passwordHasher);
        $request = $this->createMock(CreateUserRequest::class);

        $request->expects(self::exactly(2))
            ->method('email')
            ->willReturn(EmailField::fromString('existing@example.com'))
        ;
        $request->expects(self::never())->method('plainPassword');
        $request->expects(self::never())->method('roles');

        $userRepository->expects(self::once())
            ->method('emailExists')
            ->willReturn(true)
        ;
        $userRepository->expects(self::never())->method('create');
        $passwordHasher->expects(self::never())->method('hashPassword');

        // Act & Assert
        $this->expectException(EmailAlreadyExists::class);
        $useCase->execute($request);
    }
}
