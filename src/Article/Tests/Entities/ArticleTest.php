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

namespace Article\Tests\Entities;

use Article\Entities\Article;
use Article\Entities\Component\Supplier;
use Article\Entities\Component\ZoneStorage;
use Article\Entities\VO\Amount;
use Article\Entities\VO\Packaging;
use PHPUnit\Framework\TestCase;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\EmailField;
use Shared\Entities\VO\FamilyLog;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\PhoneField;
use Shared\Entities\VO\Taxes;

/**
 * @group unitTest
 */
final class ArticleTest extends TestCase
{
    public function testInstantiateArticle(): void
    {
        // Arrange && Act
        $article = Article::create(
            ResourceUuid::fromString('e5b6c68b-23d0-4e4e-ad5e-436c649da004'),
            NameField::fromString('Jambon Trad 6kg'),
            $this->getSupplier(),
            Packaging::fromArray([['Colis', 1], ['pièce', 2], ['kilogramme', 6.000]]),
            Amount::fromFloat(6.82),
            Taxes::fromFloat(5.5),
            8.8,
            [$this->getZoneStorage()],
            FamilyLog::create(
                NameField::fromString('Viande'),
                FamilyLog::create(NameField::fromString('Frais'))
            ),
            1.25
        );

        // Assert
        self::assertSame('e5b6c68b-23d0-4e4e-ad5e-436c649da004', $article->uuid()->toString());
        self::assertSame('Jambon Trad 6kg', $article->name()->toString());
        self::assertSame('Davigel', $article->supplier()->name()->toString());
        self::assertSame(['colis', 1.0], $article->packaging()->parcel());
        self::assertSame(['pièce', 2.0], $article->packaging()->subPackage());
        self::assertSame(['kilogramme', 6.0], $article->packaging()->consumerUnit());
        self::assertSame(6.82, $article->price()->toFloat());
        self::assertSame("5,50\u{a0}%", $article->taxes()->name());
        self::assertSame(8.800, $article->minStock());
        self::assertSame('Chambre positive', $article->zoneStorages()->current()->name()->toString());
        self::assertSame('viande', $article->familyLog()->slug());
        $parent = $article->familyLog()->parent();
        self::assertInstanceOf(FamilyLog::class, $parent);
        self::assertSame('frais', $parent->slug());
    }

    public function testRenameArticle(): void
    {
        // Arrange
        $article = Article::create(
            ResourceUuid::fromString('e5b6c68b-23d0-4e4e-ad5e-436c649da004'),
            NameField::fromString('Jambon Trad 6kg'),
            $this->getSupplier(),
            Packaging::fromArray([['Colis', 1], ['pièce', 2], ['kilogramme', 6.000]]),
            Amount::fromInt(682),
            Taxes::fromFloat(5.5),
            8.8,
            [$this->getZoneStorage()],
            FamilyLog::create(
                NameField::fromString('Viande'),
                FamilyLog::create(NameField::fromString('Frais'))
            )
        );

        // Act
        $article->renameArticle(NameField::fromString('Jambon Tradition 6kg'));

        // Assert
        self::assertSame('Jambon Tradition 6kg', $article->name()->toString());
        self::assertSame(682, $article->price()->toInt());
    }

    private function getZoneStorage(): ZoneStorage
    {
        return ZoneStorage::create(NameField::fromString('Chambre positive'));
    }

    private function getSupplier(): Supplier
    {
        return Supplier::create(
            ResourceUuid::fromString('a136c6fe-8f6e-45ed-91bc-586374791033'),
            NameField::fromString('Davigel'),
            '15, rue des givrés',
            '75000',
            'Paris',
            'France',
            PhoneField::fromString('+33100000001'),
            PhoneField::fromString('+33100000002'),
            EmailField::fromString('contact@davigel.fr'),
            'David',
            PhoneField::fromString('+33600000001'),
            FamilyLog::create(NameField::fromString('Frais')),
            3,
            [1, 3]
        );
    }
}
