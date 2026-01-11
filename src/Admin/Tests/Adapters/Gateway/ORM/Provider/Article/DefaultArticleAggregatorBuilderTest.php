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

namespace Admin\Tests\Adapters\Gateway\ORM\Provider\Article;

use Admin\Adapters\Gateway\ORM\Entity\ZoneStorage;
use Admin\Contracts\Services\Provider\Article\ArticleFilter;
use Admin\Contracts\Services\Provider\Article\ArticleOrderField;
use Admin\Contracts\Services\Provider\Article\ArticleProvider;
use Admin\Tests\Factory\ArticleFactory;
use Admin\Tests\Factory\FamilyLogFactory;
use Admin\Tests\Factory\SupplierFactory;
use Admin\Tests\Factory\ZoneStorageFactory;
use Admin\Tests\Story\ArticleStory;
use Shared\Entities\Enum\QueryOrder;
use Shared\Entities\ResourceUuid;
use Shared\Tests\BaseFunctionalTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * @group functionalTest
 *
 * @covers \Admin\Adapters\Gateway\Contracts\Provider\Article\DefaultArticleAggregatorBuilder
 */
final class DefaultArticleAggregatorBuilderTest extends BaseFunctionalTestCase
{
    use Factories;
    use ResetDatabase;

    public function testProvideArticlesWithoutFilters(): void
    {
        // Arrange
        ArticleStory::load();

        /** @var ArticleProvider $builder */
        $builder = self::getContainer()->get(ArticleProvider::class);

        // Act
        $articles = $builder->forArticles(articleIds: [])->provideAll();

        // Assert
        self::assertCount(4, $articles);
    }

    public function testProvideArticlesWithZoneStorageFilter(): void
    {
        // Arrange
        ArticleStory::load();

        /** @var ArticleProvider $provider */
        $provider = self::getContainer()->get(ArticleProvider::class);

        $zoneStorages = ZoneStorageFactory::findBy(['label' => 'Réserve maraîchère']);
        self::assertNotEmpty($zoneStorages, 'ZoneStorage "Réserve maraîchère" non trouvé');
        $zoneStorageUuid = ResourceUuid::fromString($zoneStorages[0]->_real()->uuid());

        // Act
        $articles = $provider->forArticles(articleIds: [])
            ->withFilter(filter: ArticleFilter::ZONE_STORAGE, value: $zoneStorageUuid)
            ->provideAll()
        ;

        // Assert
        self::assertCount(2, $articles);
    }

    public function testProvideArticlesWithMultipleZoneStorageFilter(): void
    {
        // Arrange
        ArticleStory::load();

        /** @var ArticleProvider $provider */
        $provider = self::getContainer()->get(ArticleProvider::class);

        $zoneStorages = ZoneStorageFactory::findBy(['label' => ['Réserve maraîchère', 'Réserve positive']]);
        self::assertCount(2, $zoneStorages);
        self::assertNotEmpty($zoneStorages, 'ZoneStorage "Réserve maraîchère" et "Réserve positive" non trouvés');
        $zoneStorageUuids = array_map(
            static fn (ZoneStorage $zoneStorage): ResourceUuid => ResourceUuid::fromString($zoneStorage->_real()->uuid()),
            $zoneStorages
        );

        // Act
        $articles = $provider->forArticles(articleIds: [])
            ->withFilter(filter: ArticleFilter::ZONE_STORAGE, value: $zoneStorageUuids)
            ->provideAll()
        ;

        // Assert
        self::assertCount(4, $articles);
    }

    public function testProvideArticlesWithFamilyLogFilter(): void
    {
        // Arrange
        ArticleStory::load();

        /** @var ArticleProvider $provider */
        $provider = self::getContainer()->get(ArticleProvider::class);

        $familyLogs = FamilyLogFactory::findBy(['label' => 'Frais - Fruits & Légumes']);
        self::assertNotEmpty($familyLogs, 'FamilyLog "Frais - Fruits & Légumes" non trouvé');
        $familyLogUuid = ResourceUuid::fromString($familyLogs[0]->_real()->uuid());

        // Act
        $articles = $provider->forArticles(articleIds: [])
            ->withFilter(filter: ArticleFilter::FAMILY_LOG, value: $familyLogUuid)
            ->provideAll()
        ;

        // Assert
        self::assertCount(2, $articles);
    }

    public function testProvideArticlesWithSupplierFilter(): void
    {
        // Arrange
        ArticleStory::load();

        /** @var ArticleProvider $provider */
        $provider = self::getContainer()->get(ArticleProvider::class);

        $suppliers = SupplierFactory::findBy(['name' => 'Fournisseur 1']);
        self::assertNotEmpty($suppliers, 'Supplier "Fournisseur 1" non trouvé');
        $supplierUuid = ResourceUuid::fromString($suppliers[0]->_real()->uuid());

        // Act
        $articles = $provider->forArticles(articleIds: [])
            ->withFilter(filter: ArticleFilter::SUPPLIER, value: $supplierUuid)
            ->provideAll()
        ;

        // Assert
        self::assertCount(3, $articles);
    }

    public function testProvideArticlesWithActiveFilter(): void
    {
        // Arrange
        ArticleStory::load();

        /** @var ArticleProvider $provider */
        $provider = self::getContainer()->get(ArticleProvider::class);

        // Act
        $articles = $provider->forArticles(articleIds: [])
            ->withFilter(filter: ArticleFilter::ACTIVE, value: true)
            ->provideAll()
        ;

        // Assert
        self::assertCount(4, $articles);
    }

    public function testLimitAndOffset(): void
    {
        // Arrange
        ArticleStory::load();

        /** @var ArticleProvider $provider */
        $provider = self::getContainer()->get(ArticleProvider::class);

        // Act
        $articles = $provider->forArticles(articleIds: [])
            ->limit(1)
            ->offset(0)
            ->provideAll()
        ;

        // Assert
        self::assertCount(1, $articles);
        self::assertSame(4, $articles->totalItems());
    }

    public function testOrderByName(): void
    {
        // Arrange
        ArticleStory::load();

        /** @var ArticleProvider $provider */
        $provider = self::getContainer()->get(ArticleProvider::class);

        // Act
        $articles = $provider->forArticles(articleIds: [])
            ->orderBy(field: ArticleOrderField::NAME, direction: QueryOrder::DESC)
            ->provideAll()
        ;

        // Assert
        $articlesArray = iterator_to_array($articles);
        self::assertCount(4, $articlesArray);
        self::assertSame('Carottes', $articlesArray[3]->name->toString());
        self::assertSame('Lait', $articlesArray[2]->name->toString());
        self::assertSame('Tomates', $articlesArray[1]->name->toString());
        self::assertSame('Yaourt', $articlesArray[0]->name->toString());
    }

    public function testCombinedFilters(): void
    {
        // Arrange
        ArticleStory::load();

        /** @var ArticleProvider $provider */
        $provider = self::getContainer()->get(ArticleProvider::class);

        $zoneStorages = ZoneStorageFactory::findBy(['label' => 'Réserve positive']);
        self::assertNotEmpty($zoneStorages, 'ZoneStorage "Réserve positive" non trouvé');
        $zoneStorageUuid = ResourceUuid::fromString($zoneStorages[0]->_real()->uuid());

        $suppliers = SupplierFactory::findBy(['name' => 'Fournisseur 1']);
        self::assertNotEmpty($suppliers, 'Supplier "Fournisseur 1" non trouvé');
        $supplierUuid = ResourceUuid::fromString($suppliers[0]->_real()->uuid());

        // Act
        $articles = $provider->forArticles(articleIds: [])
            ->withFilter(filter: ArticleFilter::ZONE_STORAGE, value: $zoneStorageUuid)
            ->withFilter(filter: ArticleFilter::SUPPLIER, value: $supplierUuid)
            ->provideAll()
        ;

        // Assert
        self::assertCount(1, $articles);
        $articlesArray = iterator_to_array($articles);
        self::assertSame('Yaourt', $articlesArray[0]->name->toString());
    }

    public function testArticleInMultipleZonesReturnsOneRowPerZone(): void
    {
        // Arrange
        ArticleStory::load();

        $zonePositive = ZoneStorageFactory::findBy(['label' => 'Réserve positive'])[0];
        $zoneMaraichere = ZoneStorageFactory::findBy(['label' => 'Réserve maraîchère'])[0];

        ArticleFactory::createOne([
            'name' => 'Pommes Multi-Zone',
            'zoneStorages' => [$zonePositive->_real(), $zoneMaraichere->_real()],
        ]);

        /** @var ArticleProvider $provider */
        $provider = self::getContainer()->get(ArticleProvider::class);

        $zoneUuids = [
            ResourceUuid::fromString($zonePositive->_real()->uuid()),
            ResourceUuid::fromString($zoneMaraichere->_real()->uuid()),
        ];

        // Act
        $articles = $provider->forArticles(articleIds: [])
            ->withFilter(filter: ArticleFilter::ZONE_STORAGE, value: $zoneUuids)
            ->provideAll()
        ;

        // Assert
        self::assertCount(6, $articles);

        $articlesArray = iterator_to_array($articles);
        $multiZoneRows = array_filter(
            $articlesArray,
            static fn ($article) => $article->name->toString() === 'Pommes Multi-Zone'
        );

        self::assertCount(2, $multiZoneRows);
        $zoneUuidsFromResults = array_map(
            static fn ($article) => $article->zoneStorageUuid?->toString(),
            $multiZoneRows
        );
        $zoneUuidsFromResults = array_values($zoneUuidsFromResults);

        self::assertContains($zonePositive->_real()->uuid(), $zoneUuidsFromResults);
        self::assertContains($zoneMaraichere->_real()->uuid(), $zoneUuidsFromResults);
    }
}
