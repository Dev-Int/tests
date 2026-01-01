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

namespace Admin\Tests\Entities\FamilyLog;

use Admin\Entities\Exception\FamilyLog\IsAlreadyChild;
use Admin\Entities\FamilyLog\FamilyLog;
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
        self::assertSame('alimentaire_surgele_viande', $familyLog->path());
        self::assertSame('Viande', $familyLog->label()->toString());
        self::assertNotEmpty($parent->children());
        $child = $parent->children()[0];
        self::assertSame('Viande', $child->label()->toString());
    }

    public function testFamilyLogAddChildFailCauseAlreadyExists(): void
    {
        // Arrange
        $parent = FamilyLog::create(
            ResourceUuid::generate(),
            NameField::fromString('Alimentaire')
        );
        FamilyLog::create(
            ResourceUuid::generate(),
            NameField::fromString('Surgelé'),
            $parent
        );
        $fresh = FamilyLog::create(
            ResourceUuid::generate(),
            NameField::fromString('Frais'),
            $parent
        );

        // Act && Assert
        $this->expectException(IsAlreadyChild::class);
        $parent->addChild($fresh);
    }

    public function testAssignParentFamilyLog(): void
    {
        // Arrange
        $grandParent = FamilyLog::create(
            ResourceUuid::generate(),
            NameField::fromString('Alimentaire')
        );
        $parent = FamilyLog::create(
            ResourceUuid::generate(),
            NameField::fromString('Surgelé'),
            $grandParent
        );
        $familyLog = FamilyLog::create(
            ResourceUuid::generate(),
            NameField::fromString('Viande')
        );

        // Act
        $familyLog->assignParent($parent);

        // Assert
        self::assertSame(3, $familyLog->level());
        self::assertSame(2, $parent->level());
        self::assertSame(1, $grandParent->level());
        self::assertSame($parent, $familyLog->parent());
        self::assertSame($grandParent, $parent->parent());
        self::assertSame([$familyLog], $parent->children());
        self::assertTrue($grandParent->isCompatible($familyLog));
    }

    public function testAssignNullParentReturnsToLevelZero(): void
    {
        // Arrange : FamilyLog niveau 2 avec enfant niveau 3
        $grandParent = FamilyLog::create(
            ResourceUuid::generate(),
            NameField::fromString('Alimentaire')
        );
        $parent = FamilyLog::create(
            ResourceUuid::generate(),
            NameField::fromString('Surgelé'),
            $grandParent
        );
        $familyLog = FamilyLog::create(
            ResourceUuid::generate(),
            NameField::fromString('Viande'),
            $parent
        );
        $child = FamilyLog::create(
            ResourceUuid::generate(),
            NameField::fromString('Poulet'),
            $familyLog
        );

        // Vérification état initial
        self::assertSame(3, $familyLog->level());
        self::assertSame(4, $child->level());
        self::assertSame('alimentaire_surgele_viande', $familyLog->slug());
        self::assertSame('alimentaire_surgele_viande_poulet', $child->slug());

        // Act : Assigner parent null
        $familyLog->assignParent();

        // Assert : FamilyLog revient au niveau 0, enfant au niveau 1
        self::assertSame(0, $familyLog->level());
        self::assertNull($familyLog->parent());
        self::assertSame('viande', $familyLog->slug());
        self::assertSame('viande', $familyLog->path());

        // Vérifier que l'enfant a remonté de niveau également
        self::assertSame(1, $child->level());
        self::assertSame('viande_poulet', $child->slug());
        self::assertSame('viande_poulet', $child->path());
        self::assertSame($familyLog, $child->parent());

        // Vérifier que l'ancien parent n'a plus cet enfant
        self::assertEmpty($parent->children());
    }
}
