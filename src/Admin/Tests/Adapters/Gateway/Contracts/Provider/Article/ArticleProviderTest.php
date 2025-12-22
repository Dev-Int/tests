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

namespace Admin\Tests\Adapters\Gateway\Contracts\Provider\Article;

use Admin\Adapters\Gateway\Contracts\Provider\Article\ArticleProvider;
use Admin\Contracts\Services\Provider\Exception\ArticleNotFound;
use Admin\Tests\DataBuilder\ArticleDataBuilder;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use Admin\Tests\DataBuilder\ZoneStorageDataBuilder;
use Admin\UseCases\Gateway\Finder\ArticleFinder;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Shared\Entities\ResourceUuid;

/**
 * @group unitTest
 *
 * @covers \Admin\Adapters\Gateway\Contracts\Provider\Article\ArticleProvider
 */
final class ArticleProviderTest extends TestCase
{
    public function testProvideReturnsArticleResultWhenArticleExists(): void
    {
        // Arrange
        $uuid = ResourceUuid::fromString(ArticleDataBuilder::UUID_VALID);

        $tax = (new TaxDataBuilder())->create(name: 'TVA taux réduit', rate: 5.5)->build();
        $familyLog = (new FamilyLogDataBuilder())->create(label: 'Frais')->build();
        $zoneStorage = (new ZoneStorageDataBuilder())
            ->create(label: 'Réserve positive', familyLog: $familyLog)
            ->build()
        ;
        $supplier = (new SupplierDataBuilder())->create(name: 'Supplier 1', familyLog: $familyLog)->build();
        $unit = (new UnitDataBuilder())->create(label: 'Colis', abbreviation: 'cls')->build();

        $article = (new ArticleDataBuilder())
            ->create(
                name: 'Jambon Trad 6kg',
                supplier: $supplier,
                tax: $tax,
                zoneStorages: [$zoneStorage],
                familyLog: $familyLog,
                packaging: [[$unit, 1.0], null, null]
            )
            ->withAmount(amount: 685)
            ->withQuantity(quantity: 12.5)
            ->build()
        ;

        $finder = $this->createMock(ArticleFinder::class);
        $finder->expects(self::once())
            ->method('findByUuid')
            ->with($uuid)
            ->willReturn($article)
        ;

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $provider = new ArticleProvider($finder, $entityManager);

        // Act
        $result = $provider->provide($uuid);

        // Assert
        self::assertSame($uuid->toString(), $result->uuid->toString());
        self::assertSame('Jambon Trad 6kg', $result->name->toString());
        self::assertSame(685, $result->unitPrice->toInt());
        self::assertSame(12.5, $result->quantity->toUnit());
        self::assertSame('jambon-trad-6kg', $result->slug);
    }

    public function testProvideThrowsArticleNotFoundWhenFinderReturnsNull(): void
    {
        // Arrange
        $faker = Factory::create();
        $uuid = ResourceUuid::fromString($faker->uuid());

        $finder = $this->createMock(ArticleFinder::class);
        $finder->expects(self::once())
            ->method('findByUuid')
            ->with($uuid)
            ->willReturn(null)
        ;

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $provider = new ArticleProvider($finder, $entityManager);

        // Assert
        $this->expectException(ArticleNotFound::class);

        // Act
        $provider->provide($uuid);
    }
}
