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

namespace Admin\Tests\Adapters\Gateway\ORM\Repository;

use Admin\Entities\Event\LowStockDetected;
use Admin\Entities\Exception\Article\ArticleNotFound;
use Admin\Entities\Repository\ArticleRepository;
use Admin\Tests\Factory\ArticleFactory;
use Faker\Factory;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\Quantity;
use Shared\Tests\BaseFunctionalTestCase;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 *
 * @covers \Admin\Adapters\Gateway\ORM\Repository\DoctrineArticleRepository::resetQuantities
 */
final class DoctrineArticleRepositoryResetQuantitiesTest extends BaseFunctionalTestCase
{
    use Factories;

    private ArticleRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var ArticleRepository $repository */
        $repository = self::getContainer()->get(ArticleRepository::class);
        $this->repository = $repository;
    }

    public function testResetQuantitiesUpdatesMultipleArticlesSuccessfully(): void
    {
        // Arrange
        $article1 = ArticleFactory::createOne([
            'name' => 'Tomates',
            'quantity' => 10.0,
            'minStock' => 2.0,
        ]);
        $article2 = ArticleFactory::createOne([
            'name' => 'Carottes',
            'quantity' => 20.0,
            'minStock' => 3.0,
        ]);

        $updates = [
            [
                'uuid' => ResourceUuid::fromString($article1->_real()->uuid()),
                'quantity' => Quantity::fromUnit(15.0),
            ],
            [
                'uuid' => ResourceUuid::fromString($article2->_real()->uuid()),
                'quantity' => Quantity::fromUnit(25.0),
            ],
        ];

        // Act
        $events = $this->repository->resetQuantities($updates);

        // Assert
        self::assertCount(
            0,
            $events,
            'pas d\'événement de stock bas (les nouvelles quantités sont au-dessus du minStock)'
        );

        $updatedArticle1 = $this->repository->getByUuid(ResourceUuid::fromString($article1->_real()->uuid()));
        $updatedArticle2 = $this->repository->getByUuid(ResourceUuid::fromString($article2->_real()->uuid()));

        self::assertSame(15.0, $updatedArticle1->quantity()->toUnit());
        self::assertSame(25.0, $updatedArticle2->quantity()->toUnit());
    }

    public function testResetQuantitiesReturnsLowStockEventsCorrectly(): void
    {
        // Arrange
        $article1 = ArticleFactory::createOne([
            'name' => 'Jambon',
            'quantity' => 10.0,
            'minStock' => 5.0,
        ]);
        $article2 = ArticleFactory::createOne([
            'name' => 'Fromage',
            'quantity' => 20.0,
            'minStock' => 15.0,
        ]);

        $updates = [
            [
                'uuid' => ResourceUuid::fromString($article1->_real()->uuid()),
                'quantity' => Quantity::fromUnit(3.0), // Below minStock (5.0) -> event
            ],
            [
                'uuid' => ResourceUuid::fromString($article2->_real()->uuid()),
                'quantity' => Quantity::fromUnit(10.0), // Below minStock (15.0) -> event
            ],
        ];

        // Act
        $events = $this->repository->resetQuantities($updates);

        // Assert
        self::assertCount(2, $events);
        self::assertContainsOnlyInstancesOf(LowStockDetected::class, $events, 'les deux articles devraient déclencher des événements de stock bas');

        $eventUuids = array_map(
            static fn (LowStockDetected $event): string => $event->articleUuid->toString(),
            $events
        );
        self::assertContains($article1->_real()->uuid(), $eventUuids);
        self::assertContains($article2->_real()->uuid(), $eventUuids);
    }

    public function testResetQuantitiesThrowsExceptionWhenArticleNotFound(): void
    {
        // Arrange
        $faker = Factory::create();
        $nonExistentUuid = ResourceUuid::fromString($faker->uuid());

        $updates = [
            [
                'uuid' => $nonExistentUuid,
                'quantity' => Quantity::fromUnit(10.0),
            ],
        ];

        // Assert
        $this->expectException(ArticleNotFound::class);

        // Act
        $this->repository->resetQuantities($updates);
    }

    public function testResetQuantitiesWithEmptyArrayReturnsEmptyEvents(): void
    {
        // Arrange
        ArticleFactory::createOne(['name' => 'Article existant']);

        // Act
        $events = $this->repository->resetQuantities([]);

        // Assert
        self::assertCount(0, $events);
    }
}
