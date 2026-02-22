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

namespace Shared\Tests\Entities\VO;

use PHPUnit\Framework\TestCase;
use Shared\Entities\Exception\InvalidEmailException;
use Shared\Entities\VO\EmailField;

/**
 * @group unitTest
 */
final class EmailFieldTest extends TestCase
{
    /**
     * @return iterable<string, array<mixed>>
     */
    public static function provideIsEmailEqualsCases(): iterable
    {
        yield 'email_equals' => [
            'email1' => 'test@test.fr',
            'email2' => 'test@test.fr',
            'isEquals' => true,
        ];

        yield 'email_not_equals' => [
            'email1' => 'test@test.fr',
            'email2' => 'test2@test.fr',
            'isEquals' => false,
        ];
    }

    public function testInstantiateEmailSuccessfully(): void
    {
        // Arrange && Act
        $email = EmailField::fromString('test@test.fr');

        // Assert
        self::assertSame('test@test.fr', $email->toString());
    }

    public function testCreateWithInvalidEmailThrowsADomainException(): void
    {
        // Arrange
        $this->expectException(InvalidEmailException::class);

        // Act & Assert
        EmailField::fromString('invalid.email.fr');
    }

    /**
     * @dataProvider provideIsEmailEqualsCases
     */
    public function testIsEmailEquals(string $email1, string $email2, bool $isEquals): void
    {
        // Arrange
        $email1 = EmailField::fromString($email1);
        $email2 = EmailField::fromString($email2);

        // Act
        $result = $email1->equals($email2);

        // Assert
        self::assertSame($isEquals, $result);
    }
}
