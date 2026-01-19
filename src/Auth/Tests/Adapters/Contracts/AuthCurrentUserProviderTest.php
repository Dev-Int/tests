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

namespace Auth\Tests\Adapters\Contracts;

use Auth\Adapters\Contracts\AuthCurrentUserProvider;
use Auth\Adapters\Gateway\ORM\Entity\User;
use Auth\Contracts\Exception\UnauthenticatedUser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @group unitTest
 *
 * @covers \Auth\Adapters\Contracts\AuthCurrentUserProvider
 */
final class AuthCurrentUserProviderTest extends TestCase
{
    public function testGetCurrentUserReturnsNullWhenNoToken(): void
    {
        // Arrange
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn(null)
        ;
        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $provider = new AuthCurrentUserProvider($tokenStorage, $authChecker);

        // Act
        $result = $provider->getCurrentUser();

        // Assert
        self::assertNull($result);
    }

    public function testGetCurrentUserReturnsUserDataWhenAuthenticated(): void
    {
        // Arrange
        $user = $this->createMock(User::class);
        $user->method('uuid')->willReturn('550e8400-e29b-41d4-a716-446655440000');
        $user->method('getUserIdentifier')->willReturn('test@example.com');
        $user->method('getRoles')->willReturn(['ROLE_USER', 'ROLE_ADMIN']);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token)
        ;
        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $provider = new AuthCurrentUserProvider($tokenStorage, $authChecker);

        // Act
        $result = $provider->getCurrentUser();

        // Assert
        self::assertNotNull($result);
        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $result->uuid);
        self::assertSame('test@example.com', $result->email);
        self::assertSame(['ROLE_USER', 'ROLE_ADMIN'], $result->roles);
    }

    public function testGetCurrentUserOrFailThrowsWhenNotAuthenticated(): void
    {
        // Arrange
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn(null)
        ;
        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $provider = new AuthCurrentUserProvider($tokenStorage, $authChecker);

        // Assert
        $this->expectException(UnauthenticatedUser::class);
        $this->expectExceptionMessage('No authenticated user found');

        // Act
        $provider->getCurrentUserOrFail();
    }

    public function testGetCurrentUserReturnsNullWhenUserIsNotUserInstance(): void
    {
        // Arrange - simule un UserInterface qui n'est pas notre Entity User
        // (ex: OAuth user, LDAP user, ou autre implémentation)
        $genericUser = $this->createMock(UserInterface::class);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($genericUser);

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token)
        ;
        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $provider = new AuthCurrentUserProvider($tokenStorage, $authChecker);

        // Act
        $result = $provider->getCurrentUser();

        // Assert
        self::assertNull($result);
    }

    public function testIsAuthenticatedReturnsTrueWhenUserExists(): void
    {
        // Arrange
        $user = $this->createMock(User::class);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token)
        ;
        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $provider = new AuthCurrentUserProvider($tokenStorage, $authChecker);

        // Act
        $result = $provider->isAuthenticated();

        // Assert
        self::assertTrue($result);
    }

    public function testHasRoleReturnsTrueWhenUserHasRole(): void
    {
        // Arrange
        $user = $this->createMock(User::class);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);

        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authChecker->method('isGranted')
            ->willReturnCallback(static fn (string $role): bool => \in_array($role, ['ROLE_ADMIN', 'ROLE_USER'], true))
        ;

        $provider = new AuthCurrentUserProvider($tokenStorage, $authChecker);

        // Act & Assert
        self::assertTrue($provider->hasRole('ROLE_ADMIN'));
        self::assertTrue($provider->hasRole('ROLE_USER'));
        self::assertFalse($provider->hasRole('ROLE_SUPER_ADMIN'));
    }

    public function testHasRoleReturnsFalseWhenNotAuthenticated(): void
    {
        // Arrange
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn(null);

        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $provider = new AuthCurrentUserProvider($tokenStorage, $authChecker);

        // Act & Assert
        self::assertFalse($provider->hasRole('ROLE_ADMIN'));
    }
}
