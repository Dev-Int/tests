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

namespace Admin\Tests\Adapters\Gateway\Cache;

use Admin\Adapters\Gateway\Cache\ConfigurationState;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Admin\Adapters\Gateway\Cache\ConfigurationState
 */
final class ConfigurationStateTest extends TestCase
{
    /**
     * @return iterable<string, array{hasCompany: bool, hasUnit: bool, hasTax: bool, expected: bool}>
     */
    public static function provideIsApplicationConfiguredCases(): iterable
    {
        yield 'tout configuré' => [
            'hasCompany' => true,
            'hasUnit' => true,
            'hasTax' => true,
            'expected' => true,
        ];

        yield 'company manquante' => [
            'hasCompany' => false,
            'hasUnit' => true,
            'hasTax' => true,
            'expected' => false,
        ];

        yield 'unit manquante' => [
            'hasCompany' => true,
            'hasUnit' => false,
            'hasTax' => true,
            'expected' => false,
        ];

        yield 'tax manquante' => [
            'hasCompany' => true,
            'hasUnit' => true,
            'hasTax' => false,
            'expected' => false,
        ];

        yield 'rien configuré' => [
            'hasCompany' => false,
            'hasUnit' => false,
            'hasTax' => false,
            'expected' => false,
        ];
    }

    /**
     * @return iterable<string, array{
     *     hasCompany: bool,
     *     hasUnit: bool,
     *     hasTax: bool,
     *     hasFamilyLog: bool,
     *     hasZoneStorage: bool,
     *     hasSupplier: bool,
     *     hasArticle: bool,
     *     expected: bool
     * }>
     */
    public static function provideIsApplicationReadyCases(): iterable
    {
        yield 'tout prêt' => [
            'hasCompany' => true,
            'hasUnit' => true,
            'hasTax' => true,
            'hasFamilyLog' => true,
            'hasZoneStorage' => true,
            'hasSupplier' => true,
            'hasArticle' => true,
            'expected' => true,
        ];

        yield 'application configurée mais pas prête (familyLog manquant)' => [
            'hasCompany' => true,
            'hasUnit' => true,
            'hasTax' => true,
            'hasFamilyLog' => false,
            'hasZoneStorage' => true,
            'hasSupplier' => true,
            'hasArticle' => true,
            'expected' => false,
        ];

        yield 'application configurée mais pas prête (article manquant)' => [
            'hasCompany' => true,
            'hasUnit' => true,
            'hasTax' => true,
            'hasFamilyLog' => true,
            'hasZoneStorage' => true,
            'hasSupplier' => true,
            'hasArticle' => false,
            'expected' => false,
        ];

        yield 'application non configurée (company manquante)' => [
            'hasCompany' => false,
            'hasUnit' => true,
            'hasTax' => true,
            'hasFamilyLog' => true,
            'hasZoneStorage' => true,
            'hasSupplier' => true,
            'hasArticle' => true,
            'expected' => false,
        ];

        yield 'rien configuré' => [
            'hasCompany' => false,
            'hasUnit' => false,
            'hasTax' => false,
            'hasFamilyLog' => false,
            'hasZoneStorage' => false,
            'hasSupplier' => false,
            'hasArticle' => false,
            'expected' => false,
        ];
    }

    /**
     * @dataProvider provideIsApplicationConfiguredCases
     */
    public function testIsApplicationConfigured(
        bool $hasCompany,
        bool $hasUnit,
        bool $hasTax,
        bool $expected,
    ): void {
        // Arrange
        $state = new ConfigurationState(
            hasCompany: $hasCompany,
            hasUnit: $hasUnit,
            hasTax: $hasTax,
            hasFamilyLog: false,
            hasZoneStorage: false,
            hasSupplier: false,
            hasArticle: false,
        );

        // Act & Assert
        self::assertSame($expected, $state->isApplicationConfigured());
    }

    /**
     * @dataProvider provideIsApplicationReadyCases
     */
    public function testIsApplicationReady(
        bool $hasCompany,
        bool $hasUnit,
        bool $hasTax,
        bool $hasFamilyLog,
        bool $hasZoneStorage,
        bool $hasSupplier,
        bool $hasArticle,
        bool $expected,
    ): void {
        // Arrange
        $state = new ConfigurationState(
            hasCompany: $hasCompany,
            hasUnit: $hasUnit,
            hasTax: $hasTax,
            hasFamilyLog: $hasFamilyLog,
            hasZoneStorage: $hasZoneStorage,
            hasSupplier: $hasSupplier,
            hasArticle: $hasArticle,
        );

        // Act & Assert
        self::assertSame($expected, $state->isApplicationReady());
    }
}
