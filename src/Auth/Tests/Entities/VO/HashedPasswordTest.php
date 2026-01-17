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

namespace Auth\Tests\Entities\VO;

use Auth\Entities\Exception\InvalidHashedPassword;
use Auth\Entities\VO\HashedPassword;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 *
 * @covers \Auth\Entities\VO\HashedPassword
 */
final class HashedPasswordTest extends TestCase
{
    public function testFromHashCreatesInstance(): void
    {
        // Arrange
        $hash = '$2y$13$somehashedpassword';

        // Act
        $password = HashedPassword::fromHash($hash);

        // Assert
        self::assertSame($hash, $password->toString());
    }

    public function testToStringReturnsHash(): void
    {
        // Arrange
        $hash = '$2y$13$anotherhash';
        $password = HashedPassword::fromHash($hash);

        // Act
        $result = $password->toString();

        // Assert
        self::assertSame($hash, $result);
    }

    public function testEqualsReturnsTrueForSameHash(): void
    {
        // Arrange
        $hash = '$2y$13$samehash';
        $password1 = HashedPassword::fromHash($hash);
        $password2 = HashedPassword::fromHash($hash);

        // Act & Assert
        self::assertTrue($password1->equals($password2));
    }

    public function testEqualsReturnsFalseForDifferentHash(): void
    {
        // Arrange
        $password1 = HashedPassword::fromHash('$2y$13$hash1');
        $password2 = HashedPassword::fromHash('$2y$13$hash2');

        // Act & Assert
        self::assertFalse($password1->equals($password2));
    }

    public function testFromHashThrowsExceptionForEmptyString(): void
    {
        $this->expectException(InvalidHashedPassword::class);

        HashedPassword::fromHash('');
    }

    public function testFromHashThrowsExceptionForInvalidFormat(): void
    {
        $this->expectException(InvalidHashedPassword::class);

        HashedPassword::fromHash('plaintext-not-a-hash');
    }

    public function testFromHashAcceptsBcryptFormat(): void
    {
        // Arrange
        $hash = '$2y$13$validbcrypthash';

        // Act
        $password = HashedPassword::fromHash($hash);

        // Assert - no exception thrown, hash stored correctly
        self::assertSame($hash, $password->toString());
    }

    public function testFromHashAcceptsArgon2Format(): void
    {
        // Arrange
        $hash = '$argon2id$v=19$m=65536,t=4,p=1$hash';

        // Act
        $password = HashedPassword::fromHash($hash);

        // Assert - no exception thrown, hash stored correctly
        self::assertSame($hash, $password->toString());
    }

    public function testFromHashRejectsUnknownHashFormat(): void
    {
        // A string starting with $ but not a valid hash algorithm
        $this->expectException(InvalidHashedPassword::class);

        HashedPassword::fromHash('$invalid$notarealhash');
    }
}
