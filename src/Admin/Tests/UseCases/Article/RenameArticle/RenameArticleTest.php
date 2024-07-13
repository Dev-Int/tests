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

namespace Admin\Tests\UseCases\Article\RenameArticle;

use Admin\Entities\Exception\ArticleAlreadyExistsException;
use Admin\Entities\Exception\ArticleNotFoundException;
use Admin\Tests\DataBuilder\ArticleDataBuilder;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use Admin\Tests\DataBuilder\ZoneStorageDataBuilder;
use Admin\UseCases\Article\RenameArticle\RenameArticle;
use Admin\UseCases\Article\RenameArticle\RenameArticleRequest;
use Admin\UseCases\Gateway\ArticleRepository;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 */
final class RenameArticleTest extends TestCase
{
    public function testRenameArticleWithSuccess(): void
    {
        // Arrange
        $articleRepository = $this->createMock(ArticleRepository::class);
        $familyLogParent = (new FamilyLogDataBuilder())->create('Frais')->build();
        $familyLog = (new FamilyLogDataBuilder())
            ->create('Viande')
            ->withUuid('46835a0c-3e6c-4a5c-ab80-b1d6d96b05ae')
            ->withParent($familyLogParent)
            ->build()
        ;
        $supplier = (new SupplierDataBuilder())->create('Supplier 1', $familyLogParent)->build();
        $zoneStorage = (new ZoneStorageDataBuilder())->create('Réserve froide', $familyLogParent)->build();
        $tax = (new TaxDataBuilder())->create('TVA taux réduit', 5.5)->build();
        $unit = (new UnitDataBuilder())->create('Colis', 'cls')->build();
        $article = (new ArticleDataBuilder())
            ->create(
                'Jambon Trad 6k',
                $supplier,
                $tax,
                [$zoneStorage],
                $familyLog,
                [[$unit, 1.0], null, null]
            )
            ->build()
        ;
        $useCase = new RenameArticle($articleRepository);
        $request = $this->createMock(RenameArticleRequest::class);

        $request->expects(self::exactly(2))->method('name')->willReturn('Jambon 6kg');
        $request->expects(self::once())->method('uuid')->willReturn($article->uuid()->toString());

        $articleRepository->expects(self::once())
            ->method('isExists')
            ->with('Jambon 6kg')
            ->willReturn(false)
        ;
        $articleRepository->expects(self::once())
            ->method('findByUuid')
            ->with($article->uuid()->toString())
            ->willReturn($article)
        ;

        $articleRepository->expects(self::once())
            ->method('renameArticle')
            ->with($article)
        ;

        // Act
        $response = $useCase->execute($request);
        $articleUpdated = $response->article;

        // Assert
        self::assertSame('Jambon 6kg', $articleUpdated->name()->toString());
    }

    public function testRenameArticleFailWithAlreadyExistsException(): void
    {
        // Arrange
        $articleRepository = $this->createMock(ArticleRepository::class);
        $familyLogParent = (new FamilyLogDataBuilder())->create('Frais')->build();
        $familyLog = (new FamilyLogDataBuilder())
            ->create('Viande')
            ->withUuid('46835a0c-3e6c-4a5c-ab80-b1d6d96b05ae')
            ->withParent($familyLogParent)
            ->build()
        ;
        $supplier = (new SupplierDataBuilder())->create('Supplier 1', $familyLogParent)->build();
        $zoneStorage = (new ZoneStorageDataBuilder())->create('Réserve froide', $familyLogParent)->build();
        $tax = (new TaxDataBuilder())->create('TVA taux réduit', 5.5)->build();
        $unit = (new UnitDataBuilder())->create('Colis', 'cls')->build();
        $article = (new ArticleDataBuilder())
            ->create(
                'Jambon Trad 6k',
                $supplier,
                $tax,
                [$zoneStorage],
                $familyLog,
                [[$unit, 1.0], null, null]
            )
            ->build()
        ;
        $useCase = new RenameArticle($articleRepository);
        $request = $this->createMock(RenameArticleRequest::class);

        $request->expects(self::exactly(2))->method('name')->willReturn('Jambon 6kg');
        $request->expects(self::never())->method('uuid')->willReturn($article->uuid()->toString());

        $articleRepository->expects(self::once())
            ->method('isExists')
            ->with('Jambon 6kg')
            ->willReturn(true)
        ;
        $articleRepository->expects(self::never())
            ->method('findByUuid')
            ->with($article->uuid()->toString())
            ->willReturn($article)
        ;

        $articleRepository->expects(self::never())
            ->method('renameArticle')
            ->with($article)
        ;

        // Act && Assert
        $this->expectException(ArticleAlreadyExistsException::class);
        $this->expectExceptionMessage(ArticleAlreadyExistsException::MESSAGE);
        $useCase->execute($request);
    }

    public function testRenameArticleFailWithArticleNotFoundException(): void
    {
        // Arrange
        $articleRepository = $this->createMock(ArticleRepository::class);
        $familyLogParent = (new FamilyLogDataBuilder())->create('Frais')->build();
        $familyLog = (new FamilyLogDataBuilder())
            ->create('Viande')
            ->withUuid('46835a0c-3e6c-4a5c-ab80-b1d6d96b05ae')
            ->withParent($familyLogParent)
            ->build()
        ;
        $supplier = (new SupplierDataBuilder())->create('Supplier 1', $familyLogParent)->build();
        $zoneStorage = (new ZoneStorageDataBuilder())->create('Réserve froide', $familyLogParent)->build();
        $tax = (new TaxDataBuilder())->create('TVA taux réduit', 5.5)->build();
        $unit = (new UnitDataBuilder())->create('Colis', 'cls')->build();
        $article = (new ArticleDataBuilder())
            ->create(
                'Jambon Trad 6k',
                $supplier,
                $tax,
                [$zoneStorage],
                $familyLog,
                [[$unit, 1.0], null, null]
            )
            ->build()
        ;
        $useCase = new RenameArticle($articleRepository);
        $request = $this->createMock(RenameArticleRequest::class);

        $request->expects(self::once())->method('name')->willReturn('Jambon 6kg');
        $request->expects(self::once())->method('uuid')->willReturn($article->uuid()->toString());

        $articleRepository->expects(self::once())
            ->method('isExists')
            ->with('Jambon 6kg')
            ->willReturn(false)
        ;
        $articleRepository->expects(self::once())
            ->method('findByUuid')
            ->with($article->uuid()->toString())
            ->will(self::throwException(new ArticleNotFoundException($article->uuid()->toString())))
        ;

        $articleRepository->expects(self::never())
            ->method('renameArticle')
            ->with($article)
        ;

        // Act && Assert
        $this->expectException(ArticleNotFoundException::class);
        $this->expectExceptionMessage(ArticleNotFoundException::MESSAGE);
        $useCase->execute($request);
    }
}
