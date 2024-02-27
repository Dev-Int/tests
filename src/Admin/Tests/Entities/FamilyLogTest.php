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

namespace Admin\Tests\Entities;

use Admin\Entities\FamilyLog;
use PHPUnit\Framework\TestCase;
use Shared\Entities\ResourceUuid;
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
            ResourceUuid::generate(),
            NameField::fromString('Surgelé'),
            FamilyLog::create(ResourceUuid::generate(), NameField::fromString('Alimentaire'))
        );
        $familyLog = FamilyLog::create(
            ResourceUuid::generate(),
            NameField::fromString('Viande'),
            $parent
        );

        // Assert
        self::assertSame('alimentaire-surgele-viande', $familyLog->path());
        self::assertSame('Viande', $familyLog->label()->toString());
    }

    public function testGetSmallTreeFamilyLog(): void
    {
        // Arrange && Act
        $alimentaire = FamilyLog::create(
            ResourceUuid::generate(),
            NameField::fromString('Alimentaire')
        );

        // Assert
        self::assertSame(
            [
                'Alimentaire' => [],
            ],
            $alimentaire->parseTree()
        );
    }

    public function testGetTreeFamilyLog(): void
    {
        // Arrange && Act
        $alimentaire = FamilyLog::create(
            ResourceUuid::generate(),
            NameField::fromString('Alimentaire')
        );
        $surgele = FamilyLog::create(
            ResourceUuid::generate(),
            NameField::fromString('Surgelé'),
            $alimentaire
        );
        FamilyLog::create(
            ResourceUuid::generate(),
            NameField::fromString('Frais'),
            $alimentaire
        );
        FamilyLog::create(
            ResourceUuid::generate(),
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
