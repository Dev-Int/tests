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

namespace Auth\Tests\Adapters\Gateway\Security;

use Auth\Adapters\Gateway\Security\SymfonyPasswordHasher;
use Auth\UseCases\User\CreateUser\PasswordHasherUser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @group unitTest
 *
 * @covers \Auth\Adapters\Gateway\Security\SymfonyPasswordHasher
 */
final class SymfonyPasswordHasherTest extends TestCase
{
    public function testHashPassword(): void
    {
        // Arrange
        $plainPassword = 'MySecurePassword123!';
        $expectedHash = '$2y$13$validHashedPasswordString';

        $symfonyHasher = $this->createMock(UserPasswordHasherInterface::class);
        $symfonyHasher
            ->expects(self::once())
            ->method('hashPassword')
            ->with(
                self::callback(static fn ($user) => $user instanceof PasswordHasherUser),
                $plainPassword
            )
            ->willReturn($expectedHash)
        ;

        $gateway = new SymfonyPasswordHasher($symfonyHasher);

        // Act
        $result = $gateway->hashPassword($plainPassword);

        // Assert
        self::assertSame($expectedHash, $result->toString());
    }

    public function testHashPasswordReturnsValidHashedPassword(): void
    {
        // Arrange
        $plainPassword = 'AnotherPassword456!';
        $expectedHash = '$2y$13$anotherValidHashString';

        $symfonyHasher = $this->createMock(UserPasswordHasherInterface::class);
        $symfonyHasher
            ->method('hashPassword')
            ->willReturn($expectedHash)
        ;

        $gateway = new SymfonyPasswordHasher($symfonyHasher);

        // Act
        $result = $gateway->hashPassword($plainPassword);

        // Assert
        self::assertSame($expectedHash, $result->toString());
    }
}
