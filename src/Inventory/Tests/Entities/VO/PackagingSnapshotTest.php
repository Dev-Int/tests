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

namespace Inventory\Tests\Entities\VO;

use Inventory\Entities\VO\PackagingLevel;
use Inventory\Entities\VO\PackagingSnapshot;
use Inventory\Entities\VO\RealStockComponents;
use PHPUnit\Framework\TestCase;

final class PackagingSnapshotTest extends TestCase
{
    /**
     * @return iterable<string, array{PackagingSnapshot, RealStockComponents, float, string}>
     */
    public static function provideCalculateTotalFromComponentsCases(): iterable
    {
        // Hierarchy: consumerUnit (required) → subPackage (optional) → parcel (optional)

        yield '1 niveau - 5 portions' => [
            new PackagingSnapshot(
                consumerUnit: new PackagingLevel('portion', 'prt', 1.0),
            ),
            RealStockComponents::fromUnits(consumerUnit: 5.0, subPackage: 0.0, parcel: 0.0),
            5.0,
            '5 portions = 5 unités de base',
        ];

        yield '2 niveaux - 1 poche de 8 portions + 3 portions' => [
            new PackagingSnapshot(
                consumerUnit: new PackagingLevel('portion', 'prt', 1.0),
                subPackage: new PackagingLevel('poche', 'pch', 8.0),
            ),
            RealStockComponents::fromUnits(consumerUnit: 3.0, subPackage: 1.0, parcel: 0.0),
            11.0,
            '3 portions + 1 poche (×8 portions) = 11 portions',
        ];

        yield '3 niveaux - 2 colis + 3 poches + 5 portions' => [
            new PackagingSnapshot(
                consumerUnit: new PackagingLevel('portion', 'prt', 1.0),
                subPackage: new PackagingLevel('poche', 'pch', 8.0),
                parcel: new PackagingLevel('colis', 'cls', 4.0),
            ),
            RealStockComponents::fromUnits(consumerUnit: 5.0, subPackage: 3.0, parcel: 2.0),
            93.0,
            '5 portions + 3 poches (3×8) + 2 colis (2×4×8) = 5 + 24 + 64 = 93 portions',
        ];

        yield 'quantités à zéro' => [
            new PackagingSnapshot(
                consumerUnit: new PackagingLevel('portion', 'prt', 1.0),
                subPackage: new PackagingLevel('poche', 'pch', 8.0),
            ),
            RealStockComponents::fromUnits(consumerUnit: 0.0, subPackage: 0.0, parcel: 0.0),
            0.0,
            'Aucune saisie = 0',
        ];

        yield 'décimales' => [
            new PackagingSnapshot(
                consumerUnit: new PackagingLevel('kg', 'kg', 1.0),
            ),
            RealStockComponents::fromUnits(consumerUnit: 2.5, subPackage: 0.0, parcel: 0.0),
            2.5,
            'Support des décimales',
        ];

        // Edge case : valeurs saisies pour niveaux inexistants → ignorées
        yield 'niveaux inexistants ignorés' => [
            new PackagingSnapshot(
                consumerUnit: new PackagingLevel('portion', 'prt', 1.0),
                // Pas de subPackage ni parcel
            ),
            RealStockComponents::fromUnits(consumerUnit: 3.0, subPackage: 99.0, parcel: 99.0),
            3.0, // Seul consumerUnit compte
            'Valeurs pour niveaux inexistants sont ignorées',
        ];
    }

    /**
     * @dataProvider provideCalculateTotalFromComponentsCases
     */
    public function testCalculateTotalFromComponents(
        PackagingSnapshot $packaging,
        RealStockComponents $components,
        float $expectedTotal,
        string $description,
    ): void {
        $result = $packaging->calculateTotalFromComponents($components);

        self::assertSame($expectedTotal, $result->toUnit(), $description);
    }
}
