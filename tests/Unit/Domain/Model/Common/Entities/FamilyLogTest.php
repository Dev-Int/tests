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

namespace Tests\Unit\Domain\Model\Common\Entities;

use PHPUnit\Framework\TestCase;
use Shared\Entities\VO\FamilyLog;
use Shared\Entities\VO\NameField;

class FamilyLogTest extends TestCase
{
    final public function testInstantiateFamilyLog(): void
    {
        // Arrange & Act
        $surgele = FamilyLog::create(
            NameField::fromString('Surgelé'),
            FamilyLog::create(NameField::fromString('Alimentaire'))
        );
        $familyLog = FamilyLog::create(
            NameField::fromString('Viande'),
            $surgele
        );

        // Assert
        static::assertEquals('alimentaire:surgele:viande', $familyLog->path());
    }

    final public function testGetTreeFamilyLog(): void
    {
        // Arrange
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

        // Act
        $tree = $alimentaire->parseTree();

        // Assert
        static::assertEquals(
            [
                'Alimentaire' => [
                    'Surgelé' => [
                        'Viande',
                    ],
                    'Frais',
                ],
            ],
            $tree
        );
    }
}
