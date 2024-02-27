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
use Shared\Entities\Exception\InvalidPhone;
use Shared\Entities\VO\PhoneField;

/**
 * @group unitTest
 */
final class PhoneFieldTest extends TestCase
{
    public function testInstantiatePhoneNumber(): void
    {
        // Arrange & Act
        $phone = PhoneField::fromString('+33179923223');

        // Assert
        self::assertSame('+33179923223', $phone->toNumber());
    }

    public function testCreateWithStringTooLongThrowADomainException(): void
    {
        // Arrange
        $this->expectException(InvalidPhone::class);

        // Act & Assert
        PhoneField::fromString('+55$32-55-78-85-62-49-21');
    }
}
