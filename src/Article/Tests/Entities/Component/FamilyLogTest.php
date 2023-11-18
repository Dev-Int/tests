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

namespace Article\Tests\Entities\Component;

use PHPUnit\Framework\TestCase;
use Shared\Entities\VO\FamilyLog;
use Shared\Entities\VO\NameField;

/**
 * @group unitTest
 */
final class FamilyLogTest extends TestCase
{
    public function testInstantiateFamilyLog(): void
    {
        // Arrange & Act
        $parent = FamilyLog::create(
            NameField::fromString('Surgelé'),
            FamilyLog::create(NameField::fromString('Alimentaire'))
        );
        $familyLog = FamilyLog::create(
            NameField::fromString('Viande'),
            $parent
        );

        // Assert
        self::assertSame('alimentaire:surgele:viande', $familyLog->path());
        self::assertSame('Viande', $familyLog->name()->toString());
    }

    public function testGetTreeFamilyLog(): void
    {
        // Arrange && Act
        $alimentaire = FamilyLog::create(
            NameField::fromString('Alimentaire')
        );
        $surgele = FamilyLog::create(
            NameField::fromString('Surgelé'),
            $alimentaire
        );
        FamilyLog::create(
            NameField::fromString('Frais'),
            $alimentaire
        );
        FamilyLog::create(
            NameField::fromString('Viande'),
            $surgele
        );

        // Assert
        self::assertSame(
            [
                'Alimentaire' => [
                    'Surgelé' => [
                        'Viande',
                    ],
                    'Frais',
                ],
            ],
            $alimentaire->parseTree()
        );
    }
}
