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

namespace Admin\Tests\Entities\Article;

use Admin\Entities\Article\Article;
use Admin\Entities\Event\LowStockDetected;
use Admin\Tests\DataBuilder\ArticleDataBuilder;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use Admin\Tests\DataBuilder\ZoneStorageDataBuilder;
use PHPUnit\Framework\TestCase;
use Shared\Entities\VO\Quantity;

/**
 * @group unitTest
 */
final class ArticleResetQuantityTest extends TestCase
{
    public function testResetQuantityReturnsNullWhenAboveMinStock(): void
    {
        // Arrange
        $article = $this->createArticle(minStock: 5.0, quantity: 10.0);

        // Act
        $event = $article->resetQuantity(Quantity::fromUnit(12.0));

        // Assert
        self::assertNull($event);
        self::assertSame(12.0, $article->quantity()->toUnit());
    }

    public function testResetQuantityReturnsNullWhenEqualToMinStock(): void
    {
        // Arrange
        $article = $this->createArticle(minStock: 5.0, quantity: 10.0);

        // Act
        $event = $article->resetQuantity(Quantity::fromUnit(5.0));

        // Assert
        self::assertNull($event);
        self::assertSame(5.0, $article->quantity()->toUnit());
    }

    public function testResetQuantityReturnsEventWhenBelowMinStock(): void
    {
        // Arrange
        $article = $this->createArticle(minStock: 5.0, quantity: 10.0);

        // Act
        $event = $article->resetQuantity(Quantity::fromUnit(3.0));

        // Assert
        self::assertInstanceOf(LowStockDetected::class, $event);
        self::assertSame($article->uuid()->toString(), $event->articleUuid->toString());
        self::assertSame($article->name()->toString(), $event->articleName->toString());
        self::assertSame(3.0, $event->currentQuantity->toUnit());
        self::assertSame(5.0, $event->minStock);
    }

    public function testResetQuantityReturnsEventWhenZeroQuantity(): void
    {
        // Arrange
        $article = $this->createArticle(minStock: 5.0, quantity: 10.0);

        // Act
        $event = $article->resetQuantity(Quantity::fromUnit(0.0));

        // Assert
        self::assertInstanceOf(LowStockDetected::class, $event);
        self::assertSame(0.0, $event->currentQuantity->toUnit());
    }

    public function testResetQuantityUpdatesQuantityValue(): void
    {
        // Arrange
        $article = $this->createArticle(minStock: 5.0, quantity: 10.0);
        self::assertSame(10.0, $article->quantity()->toUnit());

        // Act
        $article->resetQuantity(Quantity::fromUnit(7.5));

        // Assert
        self::assertSame(7.5, $article->quantity()->toUnit());
    }

    private function createArticle(float $minStock, float $quantity): Article
    {
        $familyLog = (new FamilyLogDataBuilder())->create('Alimentaire')->build();
        $supplier = (new SupplierDataBuilder())->create('Supplier Test', $familyLog)->build();
        $tax = (new TaxDataBuilder())->create('TVA 5.5%', 5.5)->build();
        $zoneStorage = (new ZoneStorageDataBuilder())->create('Reserve', $familyLog)->build();
        $unit = (new UnitDataBuilder())->create('Colis', 'COL')->build();

        return (new ArticleDataBuilder())
            ->create(
                'Article Test',
                $supplier,
                $tax,
                [$zoneStorage],
                $familyLog,
                [[$unit, 1.0], null, null]
            )
            ->withMinStock($minStock)
            ->withQuantity($quantity)
            ->build()
        ;
    }
}
