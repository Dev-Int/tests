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
use Admin\Entities\Exception\InvalidUnitException;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 */
final class StorageTest extends TestCase
{
    public function testInstantiateStorage(): void
    {
        // Arrange
        $unit = (new UnitDataBuilder())->create('Colis', 'cls')->build();

        // Act
        $storage = Storage::fromArray([$unit, 1.0]);

        // Assert
        self::assertSame([$unit, 1.0], $storage->toArray());
        self::assertSame('colis', $storage->unit()->slug());
        self::assertSame(1.0, $storage->quantity());
    }

    public function testInvalidUnitStorage(): void
    {
        // Arrange
        $unit = (new UnitDataBuilder())->create('Bad unit', 'bd')->build();

        // Act && Assert
        $this->expectException(InvalidUnitException::class);
        $this->expectExceptionMessage(InvalidUnitException::MESSAGE);
        Storage::fromArray([$unit, 1.0]);
    }
}
