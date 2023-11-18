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
use Shared\Entities\Exception\InvalidEmail;
use Shared\Entities\VO\EmailField;

/**
 * @group unitTest
 */
final class EmailFieldTest extends TestCase
{
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
        $this->expectException(InvalidEmail::class);

        // Act & Assert
        EmailField::fromString('invalid.email.fr');
    }
}
