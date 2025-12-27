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
use Inventory\Entities\VO\RealStockEntry;
use PHPUnit\Framework\TestCase;

final class PackagingSnapshotTest extends TestCase
{
    /**
     * @return iterable<string, array{PackagingSnapshot, RealStockEntry, float, string}>
     */
    public static function provideCalculateTotalFromEntryCases(): iterable
    {
        yield '1 niveau - 5 colis' => [
            new PackagingSnapshot(
                parcel: new PackagingLevel('colis', 'cls', 1.0),
            ),
            new RealStockEntry(parcelQuantity: 5.0),
            5.0,
            '5 colis sans sous-niveaux = 5 unités',
        ];

        yield '2 niveaux - 2 colis de 4 poches' => [
            new PackagingSnapshot(
                parcel: new PackagingLevel('colis', 'cls', 1.0),
                subPackage: new PackagingLevel('poche', 'pch', 4.0),
            ),
            new RealStockEntry(parcelQuantity: 2.0, subPackageQuantity: 1.0),
            9.0,
            '2 colis (×4 poches) + 1 poche = 9 poches',
        ];

        yield '3 niveaux - 2 colis de 4 poches + 4 poches de 8 portions + 5 portions' => [
            new PackagingSnapshot(
                parcel: new PackagingLevel('colis', 'cls', 1.0),
                subPackage: new PackagingLevel('poche', 'pch', 4.0),
                consumerUnit: new PackagingLevel('portion', 'prt', 8.0),
            ),
            new RealStockEntry(parcelQuantity: 2.0, subPackageQuantity: 3.0, consumerUnitQuantity: 5.0),
            93.0,
            '2 colis (2x4x8) + 3 poches (3x8) + 5 portions = 93 portions',
        ];

        yield 'quantités à zéro' => [
            new PackagingSnapshot(
                parcel: new PackagingLevel('colis', 'cls', 1.0),
                subPackage: new PackagingLevel('poche', 'pch', 4.0),
            ),
            new RealStockEntry(parcelQuantity: 0.0, subPackageQuantity: 0.0),
            0.0,
            'Aucune saisie = 0',
        ];

        yield 'décimales' => [
            new PackagingSnapshot(
                parcel: new PackagingLevel('colis', 'cls', 1.0),
            ),
            new RealStockEntry(parcelQuantity: 2.5),
            2.5,
            'Support des décimales',
        ];

        // Edge case : valeurs saisies pour niveaux inexistants → ignorées
        yield 'niveaux inexistants ignorés' => [
            new PackagingSnapshot(
                parcel: new PackagingLevel('colis', 'cls', 1.0),
                // Pas de subPackage ni consumerUnit
            ),
            new RealStockEntry(parcelQuantity: 3.0, subPackageQuantity: 99.0, consumerUnitQuantity: 99.0),
            3.0, // Seul parcel compte
            'Valeurs pour niveaux inexistants sont ignorées',
        ];
    }

    /**
     * @dataProvider provideCalculateTotalFromEntryCases
     */
    public function testCalculateTotalFromEntry(
        PackagingSnapshot $packaging,
        RealStockEntry $entry,
        float $expectedTotal,
        string $description,
    ): void {
        $result = $packaging->calculateTotalFromEntry($entry);

        self::assertSame($expectedTotal, $result->toUnit(), $description);
    }
}
