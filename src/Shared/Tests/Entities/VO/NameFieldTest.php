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
use Shared\Entities\Exception\StringExceeds255CharactersException;
use Shared\Entities\VO\NameField;

/**
 * @group unitTest
 */
final class NameFieldTest extends TestCase
{
    public function testCreateWithNameTooLongThrowsADomainException(): void
    {
        // Arrange
        $this->expectException(StringExceeds255CharactersException::class);

        // Act & Assert
        NameField::fromString(str_repeat('a', 256));
    }

    public function testSlugify(): void
    {
        // Arrange && Act
        $name = NameField::fromString('Test slugify');

        // Assert
        self::assertSame('test-slugify', $name->slugify());
        self::assertSame('Test slugify', $name->toString());
    }

    public function testSlugifyWithOption(): void
    {
        // Arrange && Act
        $name = NameField::fromString('Test slugify');

        // Assert
        self::assertSame('test_slugify', $name->slugify('_'));
        self::assertSame('Test slugify', $name->toString());
    }
}
