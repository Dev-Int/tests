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

namespace Inventory\Tests\Entities;

use Inventory\Entities\Exception\NoArticlesToLoad;
use Inventory\Entities\VO\Article;
use Inventory\Tests\Factory\InventoryFakerFactory;
use PHPUnit\Framework\TestCase;
use Shared\Entities\Exception\DomainException;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\Amount;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\Quantity;

/**
 * @group unitTest
 *
 * @covers \Inventory\Entities\Inventory::loadArticles
 */
final class InventoryLoadArticlesTest extends TestCase
{
    public function testLoadArticlesAddsItemsToInventory(): void
    {
        // Arrange
        $inventory = (new InventoryFakerFactory())->createDraft()->build();
        $articles = $this->createArticles(3);

        // Act
        $inventory->loadArticles($articles);

        // Assert
        self::assertCount(3, $inventory->items()->toArray());
    }

    public function testLoadArticlesClearsExistingItemsFirst(): void
    {
        // Arrange
        $inventory = (new InventoryFakerFactory())->createDraft()->build();
        $initialArticles = $this->createArticles(2);
        $inventory->loadArticles($initialArticles);
        self::assertCount(2, $inventory->items()->toArray());

        $newArticles = $this->createArticles(3);

        // Act
        $inventory->loadArticles($newArticles);

        // Assert
        self::assertCount(3, $inventory->items()->toArray());
    }

    public function testLoadArticlesThrowsIfNoArticles(): void
    {
        // Arrange
        $inventory = (new InventoryFakerFactory())->createDraft()->build();

        // Act & Assert
        try {
            $inventory->loadArticles([]);
            self::fail('Expected NoArticlesToLoad exception was not thrown');
        } catch (NoArticlesToLoad $exception) {
            self::assertSame(NoArticlesToLoad::MESSAGE, $exception->getMessage());
            self::assertSame(DomainException::INVALID_ARGUMENT_CODE, $exception->getCode());
        }
    }

    /**
     * @return array<Article>
     */
    private function createArticles(int $count): array
    {
        $articles = [];
        for ($i = 0; $i < $count; ++$i) {
            $articles[] = new Article(
                uuid: ResourceUuid::generate(),
                zoneStorageUuid: ResourceUuid::generate(),
                name: NameField::fromString("Article {$i}"),
                unitPrice: Amount::fromCents(1000 + $i * 100),
                quantity: Quantity::fromMilliemes(1000),
                slug: "article-{$i}",
            );
        }

        return $articles;
    }
}
