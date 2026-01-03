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

use Admin\Entities\Article\VO\Packaging;
use Admin\Entities\Unit\Unit;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 */
final class PackagingTest extends TestCase
{
    /**
     * @return iterable<string, array<array<int, array{Unit, float}|null>>>
     */
    public static function provideDistributeTheSubdivisionCases(): iterable
    {
        $unitDataBuilder = new UnitDataBuilder();

        // Format: [consumerUnit, subPackage, parcel]
        yield 'full distribution' => [
            'packaging' => [
                [$unitDataBuilder->create('Portion', 'port')->build(), 32.0],
                [$unitDataBuilder->create('Poche', 'poc')->build(), 4.0],
                [$unitDataBuilder->create('Colis', 'cls')->build(), 1.0],
            ],
            'expected' => [
                [$unitDataBuilder->create('Portion', 'port')->build(), 32.0],
                [$unitDataBuilder->create('Poche', 'poc')->build(), 4.0],
                [$unitDataBuilder->create('Colis', 'cls')->build(), 1.0],
            ],
        ];

        yield 'distribution without parcel' => [
            'packaging' => [
                [$unitDataBuilder->create('Portion', 'port')->build(), 32.0],
                [$unitDataBuilder->create('Poche', 'poc')->build(), 4.0],
                null,
            ],
            'expected' => [
                [$unitDataBuilder->create('Portion', 'port')->build(), 32.0],
                [$unitDataBuilder->create('Poche', 'poc')->build(), 4.0],
                null,
            ],
        ];

        yield 'distribution without sub package' => [
            'packaging' => [
                [$unitDataBuilder->create('Portion', 'port')->build(), 32.0],
                null,
                [$unitDataBuilder->create('Colis', 'cls')->build(), 1.0],
            ],
            'expected' => [
                [$unitDataBuilder->create('Portion', 'port')->build(), 32.0],
                null,
                [$unitDataBuilder->create('Colis', 'cls')->build(), 1.0],
            ],
        ];

        yield 'distribution without sub package and float' => [
            'packaging' => [
                [$unitDataBuilder->create('Kilogramme', 'kg')->build(), 6.000],
                null,
                [$unitDataBuilder->create('Colis', 'cls')->build(), 1.0],
            ],
            'expected' => [
                [$unitDataBuilder->create('Kilogramme', 'kg')->build(), 6.000],
                null,
                [$unitDataBuilder->create('Colis', 'cls')->build(), 1.0],
            ],
        ];

        yield 'distribution only consumer unit' => [
            'packaging' => [
                [$unitDataBuilder->create('Portion', 'port')->build(), 1.0],
                null,
                null,
            ],
            'expected' => [
                [$unitDataBuilder->create('Portion', 'port')->build(), 1.0],
                null,
                null,
            ],
        ];
    }

    /**
     * @dataProvider provideDistributeTheSubdivisionCases
     *
     * @param array{array{Unit, float}, array{Unit, float}|null, array{Unit, float}|null} $packaging [consumerUnit, subPackage, parcel]
     * @param array{array{Unit, float}, array{Unit, float}|null, array{Unit, float}|null} $expected  [consumerUnit, subPackage, parcel]
     */
    public function testDistributeTheSubdivision(array $packaging, array $expected): void
    {
        // Arrange && Act
        $packages = new Packaging($packaging[0], $packaging[1], $packaging[2]);

        // Assert
        self::assertEquals($expected[0], $packages->consumerUnit());
        self::assertEquals($expected[1], $packages->subPackage());
        self::assertEquals($expected[2], $packages->parcel());
    }
}
