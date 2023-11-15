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
            6.82,
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
        self::assertEquals('e5b6c68b-23d0-4e4e-ad5e-436c649da004', $article->uuid()->toString());
        self::assertEquals('Jambon Trad 6kg', $article->name()->getValue());
        self::assertEquals('Davigel', $article->supplier()->name()->getValue());
        self::assertEquals(['colis', 1.0], $article->packaging()->parcel());
        self::assertEquals(6.82, $article->price());
        self::assertEquals("5,50\u{a0}%", $article->taxes()->name());
        self::assertSame(8.800, $article->minStock());
    }

    public function testRenameArticle(): void
    {
        // Arrange
        $article = Article::create(
            ResourceUuid::fromString('e5b6c68b-23d0-4e4e-ad5e-436c649da004'),
            NameField::fromString('Jambon Trad 6kg'),
            $this->getSupplier(),
            Packaging::fromArray([['Colis', 1], ['pièce', 2], ['kilogramme', 6.000]]),
            6.82,
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
        self::assertEquals('Jambon Tradition 6kg', $article->name()->getValue());
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
