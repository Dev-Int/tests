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

namespace Admin\Tests\UseCases\Article\GetArticles;

use Admin\Entities\Article\ArticleCollection;
use Admin\Entities\Repository\ArticleRepository;
use Admin\Tests\DataBuilder\ArticleDataBuilder;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use Admin\Tests\DataBuilder\ZoneStorageDataBuilder;
use Admin\UseCases\Article\GetArticles\GetArticles;
use Admin\UseCases\Article\GetArticles\GetArticlesRequest;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 */
final class GetArticlesTest extends TestCase
{
    public function testGetArticlesWithSuccess(): void
    {
        // Arrange
        $articleRepository = $this->createMock(ArticleRepository::class);
        $useCase = new GetArticles($articleRepository);
        $request = $this->createMock(GetArticlesRequest::class);

        $tax = (new TaxDataBuilder())->create('TVA taux réduit', 5.5)->build();
        $familyLog = (new FamilyLogDataBuilder())->create('Frais')->build();
        $zoneStorage = (new ZoneStorageDataBuilder())->create('Réserve positive', $familyLog)->build();
        $supplier = (new SupplierDataBuilder())->create('Supplier 1', $familyLog)->build();
        $unit = (new UnitDataBuilder())->create('Colis', 'cls')->build();
        $articleDataBuilder = new ArticleDataBuilder();
        $article1 = $articleDataBuilder
            ->create(
                'Jambon Trad 6kg',
                $supplier,
                $tax,
                [$zoneStorage],
                $familyLog,
                [[$unit, 1.0], null, null]
            )
            ->build()
        ;
        $article2 = $articleDataBuilder
            ->create(
                'Lait x6 litres',
                $supplier,
                $tax,
                [$zoneStorage],
                $familyLog,
                [[$unit, 1.0], null, null]
            )
            ->build()
        ;
        $articles = new ArticleCollection(totalItems: 2);
        $articles->add($article1);
        $articles->add($article2);

        $request->expects(self::once())->method('page')->willReturn(1);
        $request->expects(self::once())->method('itemsPerPage')->willReturn(10);

        $articleRepository->expects(self::once())
            ->method('getAllArticlesPaginated')
            ->with(1, 10)
            ->willReturn($articles)
        ;

        // Act
        $response = $useCase->execute($request);
        $getArticles = $response->articles;

        // Assert
        self::assertCount(2, $getArticles);
        $getArticle1 = $getArticles->current();
        self::assertSame('Jambon Trad 6kg', $getArticle1->name()->toString());
        self::assertSame('jambon-trad-6kg', $getArticle1->slug());
        $getArticles->next();
        $getArticle2 = $getArticles->current();
        self::assertSame('Lait x6 litres', $getArticle2->name()->toString());
        self::assertSame('lait-x6-litres', $getArticle2->slug());
    }
}
