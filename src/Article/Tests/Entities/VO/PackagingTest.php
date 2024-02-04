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

use Article\Entities\VO\Packaging;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 */
final class PackagingTest extends TestCase
{
    /**
     * @dataProvider provideDistributeTheSubdivisionCases
     *
     * @param array<array{string, float}|null> $packaging
     * @param array<array{string, float}|null> $expected
     */
    public function testDistributeTheSubdivision(array $packaging, array $expected): void
    {
        // Arrange && Act
        $packages = Packaging::fromArray($packaging);

        // Assert
        self::assertEquals($expected[0], $packages->parcel());
        self::assertEquals($expected[1], $packages->subPackage());
        self::assertEquals($expected[2], $packages->consumerUnit());
    }

    /**
     * @return iterable<string, array<array<int, array{string, float}|null>>>
     */
    public function provideDistributeTheSubdivisionCases(): iterable
    {
        yield 'full distribution' => [
            'packaging' => [['colis', 1.0], ['poche', 4.0], ['portion', 32.0]],
            'expected' => [['colis', 1.0], ['poche', 4.0], ['portion', 32.0]],
        ];

        yield 'distribution without consumer unit' => [
            'packaging' => [['colis', 1.0], ['poche', 4.0], null],
            'expected' => [['colis', 1.0], ['poche', 4.0], null],
        ];

        yield 'distribution without sub package' => [
            'packaging' => [['colis', 1.0], null, ['portion', 32.0]],
            'expected' => [['colis', 1.0], null, ['portion', 32.0]],
        ];

        yield 'distribution without sub package and float' => [
            'packaging' => [['colis', 1.0], null, ['kilogramme', 6.000]],
            'expected' => [['colis', 1.0], null, ['kilogramme', 6.000]],
        ];

        yield 'distribution only parcel' => [
            'packaging' => [['colis', 1.0], null, null],
            'expected' => [['colis', 1.0], null, null],
        ];
    }
}
