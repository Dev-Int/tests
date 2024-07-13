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

use Admin\Entities\Unit\Unit;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use PHPUnit\Framework\TestCase;
use Shared\Entities\VO\Packaging;

/**
 * @group unitTest
 */
final class PackagingTest extends TestCase
{
    /**
     * @dataProvider provideDistributeTheSubdivisionCases
     *
     * @param array{array{Unit, float}, array{Unit, float}|null, array{Unit, float}|null} $packaging
     * @param array{array{Unit, float}, array{Unit, float}|null, array{Unit, float}|null} $expected
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
     * @return iterable<string, array<array<int, array{Unit, float}|null>>>
     */
    public function provideDistributeTheSubdivisionCases(): iterable
    {
        $unitDataBuilder = new UnitDataBuilder();

        yield 'full distribution' => [
            'packaging' => [[$unitDataBuilder->create('Colis', 'cls')->build(), 1.0],
                [$unitDataBuilder->create('Poche', 'poc')->build(), 4.0],
                [$unitDataBuilder->create('Portion', 'port')->build(), 32.0]],
            'expected' => [[$unitDataBuilder->create('Colis', 'cls')->build(), 1.0],
                [$unitDataBuilder->create('Poche', 'poc')->build(), 4.0],
                [$unitDataBuilder->create('Portion', 'port')->build(), 32.0]],
        ];

        yield 'distribution without consumer unit' => [
            'packaging' => [[$unitDataBuilder->create('Colis', 'cls')->build(), 1.0],
                [$unitDataBuilder->create('Poche', 'poc')->build(), 4.0],
                null],
            'expected' => [[$unitDataBuilder->create('Colis', 'cls')->build(), 1.0],
                [$unitDataBuilder->create('Poche', 'poc')->build(), 4.0],
                null],
        ];

        yield 'distribution without sub package' => [
            'packaging' => [[$unitDataBuilder->create('Colis', 'cls')->build(), 1.0],
                null,
                [$unitDataBuilder->create('Portion', 'port')->build(), 32.0]],
            'expected' => [[$unitDataBuilder->create('Colis', 'cls')->build(), 1.0],
                null,
                [$unitDataBuilder->create('Portion', 'port')->build(), 32.0]],
        ];

        yield 'distribution without sub package and float' => [
            'packaging' => [[$unitDataBuilder->create('Colis', 'cls')->build(), 1.0],
                null,
                [$unitDataBuilder->create('Kilogramme', 'kg')->build(), 6.000]],
            'expected' => [[$unitDataBuilder->create('Colis', 'cls')->build(), 1.0],
                null,
                [$unitDataBuilder->create('Kilogramme', 'kg')->build(), 6.000]],
        ];

        yield 'distribution only parcel' => [
            'packaging' => [[$unitDataBuilder->create('Colis', 'cls')->build(), 1.0], null, null],
            'expected' => [[$unitDataBuilder->create('Colis', 'cls')->build(), 1.0], null, null],
        ];
    }
}
