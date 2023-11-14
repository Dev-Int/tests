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
use Shared\Entities\Exception\StringExceeds255Characters;
use Shared\Entities\VO\NameField;

class NameFieldTest extends TestCase
{
    final public function testCreateWithNameTooLongThrowsADomainException(): void
    {
        // Arrange
        $this->expectException(StringExceeds255Characters::class);

        // Act & Assert
        NameField::fromString(str_repeat('a', 256));
    }

    final public function testSlugify(): void
    {
        // Arrange
        $name = NameField::fromString('Test slugify');

        // Act
        $slug = $name->slugify();

        // Assert
        static::assertEquals('test-slugify', $slug);
    }
}
