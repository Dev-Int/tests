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

namespace Article\Tests\Entities\VO;

use Article\Entities\VO\Storage;
use PHPUnit\Framework\TestCase;

final class StorageTest extends TestCase
{
    public function testInstantiateStorage(): void
    {
        // Arrange & Act
        $storage = Storage::fromArray(['Colis', 1]);

        // Assert
        self::assertEquals(
            new Storage('colis', 1),
            $storage
        );
    }
}
