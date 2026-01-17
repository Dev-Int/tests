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

namespace Auth\Tests\Adapters\Security;

use Auth\Adapters\Gateway\ORM\Entity\User as UserOrm;
use Auth\Adapters\Security\SymfonyUserProvider;
use Auth\Entities\Exception\UserNotFoundByEmail;
use Auth\Entities\Repository\UserRepository;
use Auth\Entities\Role;
use Auth\Tests\DataBuilder\UserDataBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\Clock\FrozenClock;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\EmailField;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @group unitTest
 *
 * @covers \Auth\Adapters\Security\SymfonyUserProvider
 */
final class SymfonyUserProviderTest extends TestCase
{
    private MockObject&UserRepository $userRepository;
    private SymfonyUserProvider $provider;

    protected function setUp(): void
    {
        ClockFactory::initialize(new FrozenClock(new \DateTimeImmutable('2026-01-15 10:00:00')));

        $this->userRepository = $this->createMock(UserRepository::class);
        $this->provider = new SymfonyUserProvider($this->userRepository);
    }

    public function testLoadUserByIdentifierReturnsOrmUser(): void
    {
        // Arrange
        $domainUser = UserDataBuilder::aUser()
            ->withEmail('test@example.com')
            ->withRoles([Role::USER, Role::ADMIN])
            ->build()
        ;

        $this->userRepository
            ->expects(self::once())
            ->method('getByEmail')
            ->with(self::callback(
                static fn (EmailField $email) => $email->toString() === 'test@example.com'
            ))
            ->willReturn($domainUser)
        ;

        // Act
        $user = $this->provider->loadUserByIdentifier('test@example.com');

        // Assert
        self::assertSame('test@example.com', $user->getUserIdentifier());
        self::assertContains('ROLE_ADMIN', $user->getRoles());
    }

    public function testLoadUserByIdentifierThrowsOnNotFound(): void
    {
        // Arrange
        $this->userRepository
            ->expects(self::once())
            ->method('getByEmail')
            ->willThrowException(new UserNotFoundByEmail(EmailField::fromString('notfound@example.com')))
        ;

        // Assert
        $this->expectException(UserNotFoundException::class);

        // Act
        $this->provider->loadUserByIdentifier('notfound@example.com');
    }

    public function testRefreshUserReturnsUpdatedUser(): void
    {
        // Arrange
        $uuid = ResourceUuid::fromString('550e8400-e29b-41d4-a716-446655440000');
        $domainUser = UserDataBuilder::aUser()
            ->withUuid($uuid)
            ->withEmail('refreshed@example.com')
            ->build()
        ;
        $ormUser = UserOrm::fromDomain($domainUser);

        $this->userRepository
            ->expects(self::once())
            ->method('getByUuid')
            ->with(self::callback(
                static fn (ResourceUuid $u) => $u->toString() === $uuid->toString()
            ))
            ->willReturn($domainUser)
        ;

        // Act
        $refreshedUser = $this->provider->refreshUser($ormUser);

        // Assert
        self::assertSame('refreshed@example.com', $refreshedUser->getUserIdentifier());
    }

    public function testRefreshUserThrowsForUnsupportedUser(): void
    {
        // Arrange
        $unsupportedUser = new class implements UserInterface {
            public function getRoles(): array
            {
                return [];
            }

            public function eraseCredentials(): void
            {
            }

            public function getUserIdentifier(): string
            {
                return 'unsupported';
            }
        };

        // Assert
        $this->expectException(UnsupportedUserException::class);

        // Act
        $this->provider->refreshUser($unsupportedUser);
    }

    public function testSupportsClassReturnsTrueForOrmUser(): void
    {
        // Act & Assert
        self::assertTrue($this->provider->supportsClass(UserOrm::class));
    }

    public function testSupportsClassReturnsFalseForOtherClass(): void
    {
        // Act & Assert
        self::assertFalse($this->provider->supportsClass(\stdClass::class));
    }

    public function testLoadUserByIdentifierThrowsForDisabledUser(): void
    {
        // Arrange
        $domainUser = UserDataBuilder::aUser()
            ->withEmail('disabled@example.com')
            ->withDisabledAt(new \DateTimeImmutable('2026-01-10 12:00:00'))
            ->build()
        ;

        $this->userRepository
            ->expects(self::once())
            ->method('getByEmail')
            ->willReturn($domainUser)
        ;

        // Assert
        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('User "disabled@example.com" is disabled.');

        // Act
        $this->provider->loadUserByIdentifier('disabled@example.com');
    }
}
