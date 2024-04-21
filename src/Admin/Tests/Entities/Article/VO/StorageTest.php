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

namespace Admin\Tests\Entities\Article\VO;

use Admin\Entities\Article\VO\Storage;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 */
final class StorageTest extends TestCase
{
    public function testInstantiateStorage(): void
    {
        // Arrange & Act
        $storage = Storage::fromArray(['Colis', 1]);

        // Assert
        self::assertSame(['colis', 1.0], $storage->toArray());
        self::assertSame('colis', $storage->unit());
        self::assertSame(1.0, $storage->quantity());
    }
}
