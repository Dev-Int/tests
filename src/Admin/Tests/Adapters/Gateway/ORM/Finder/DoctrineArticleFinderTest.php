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

namespace Admin\Tests\Adapters\Gateway\ORM\Finder;

use Admin\Entities\Article\Article;
use Admin\Tests\Factory\ArticleFactory;
use Admin\UseCases\Gateway\Finder\ArticleFinder;
use Faker\Factory;
use Shared\Entities\ResourceUuid;
use Shared\Tests\BaseFunctionalTestCase;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 *
 * @covers \Admin\Adapters\Gateway\ORM\Finder\DoctrineArticleFinder
 */
final class DoctrineArticleFinderTest extends BaseFunctionalTestCase
{
    use Factories;

    private ArticleFinder $finder;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var ArticleFinder $finder */
        $finder = self::getContainer()->get(ArticleFinder::class);
        $this->finder = $finder;
    }

    public function testFindByUuidReturnsArticleWhenItExists(): void
    {
        // Arrange
        $articleOrm = ArticleFactory::createOne([
            'name' => 'Jambon Trad 6kg',
            'unitPrice' => 685,
        ]);
        $uuid = $articleOrm->_real()->uuid();

        // Act
        $result = $this->finder->findByUuid(uuid: $uuid);

        // Assert
        self::assertInstanceOf(Article::class, $result);
        self::assertSame($uuid, $result->uuid()->toString());
        self::assertSame('Jambon Trad 6kg', $result->name()->toString());
        self::assertSame(685, $result->unitPrice()->toInt());
    }

    public function testFindByUuidReturnsNullWhenArticleDoesNotExist(): void
    {
        // Arrange
        $faker = Factory::create();
        $nonExistentUuid = ResourceUuid::fromString($faker->uuid());

        // Act
        $result = $this->finder->findByUuid(uuid: $nonExistentUuid);

        // Assert
        self::assertNull($result);
    }

    public function testFindAllArticlesReturnsAllArticles(): void
    {
        // Arrange
        ArticleFactory::createOne(['name' => 'Tomates']);
        ArticleFactory::createOne(['name' => 'Carottes']);
        ArticleFactory::createOne(['name' => 'Pommes de terre']);

        // Act
        $result = iterator_to_array($this->finder->findAllArticles());

        // Assert
        self::assertCount(3, $result);
        self::assertContainsOnlyInstancesOf(Article::class, $result);
    }

    public function testFindByUuidsReturnsMatchingArticles(): void
    {
        // Arrange
        $article1 = ArticleFactory::createOne(['name' => 'Tomates']);
        $article2 = ArticleFactory::createOne(['name' => 'Carottes']);
        ArticleFactory::createOne(['name' => 'Pommes de terre']);

        $uuids = [
            $article1->_real()->uuid(),
            $article2->_real()->uuid(),
        ];

        // Act
        $result = iterator_to_array($this->finder->findByUuids(uuids: $uuids));

        // Assert
        self::assertCount(2, $result);
        self::assertContainsOnlyInstancesOf(Article::class, $result);
    }

    public function testFindByUuidsWithEmptyArrayReturnsNothing(): void
    {
        // Arrange
        ArticleFactory::createOne(['name' => 'Tomates']);

        // Act
        $result = iterator_to_array($this->finder->findByUuids(uuids: []));

        // Assert
        self::assertCount(0, $result);
    }
}
