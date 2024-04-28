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

namespace Admin\Tests\UseCases\Article\ChangeStorageInformation;

use Admin\Tests\DataBuilder\ArticleDataBuilder;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Admin\Tests\DataBuilder\ZoneStorageDataBuilder;
use Admin\UseCases\Article\ChangeStorageInformation\ChangeArticleStorageInformation;
use Admin\UseCases\Article\ChangeStorageInformation\ChangeArticleStorageInformationRequest;
use Admin\UseCases\Gateway\ArticleRepository;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 */
final class ChangeArticleStorageInformationTest extends TestCase
{
    public function testChangeStorageInformationWithSuccess(): void
    {
        // Arrange
        $articleRepository = $this->createMock(ArticleRepository::class);
        $useCase = new ChangeArticleStorageInformation($articleRepository);
        $request = $this->createMock(ChangeArticleStorageInformationRequest::class);
        $frais = (new FamilyLogDataBuilder())->create('Frais')->build();
        $fraisViande = (new FamilyLogDataBuilder())->create('Viande')
            ->withUuid('46835a0c-3e6c-4a5c-ab80-b1d6d96b05ae')
            ->withParent($frais)
            ->build()
        ;
        $supplier1 = (new SupplierDataBuilder())->create('Supplier 1', $frais)->build();
        $storageFrais = (new ZoneStorageDataBuilder())->create('Réserve positive', $frais)->build();
        $tax = (new TaxDataBuilder())->create('TVA taux réduit', 5.5)->build();
        $article = (new ArticleDataBuilder())
            ->create('Jambon Trad 6kg', $supplier1, $tax, [$storageFrais], $fraisViande)
            ->build()
        ;

        $request->expects(self::once())->method('packaging')->willReturn([['Colis', 1], null, ['kilogramme', 6.000]]);
        $request->expects(self::once())->method('minStock')->willReturn(12.000);
        $request->expects(self::once())->method('quantity')->willReturn(25.0);
        $request->expects(self::once())->method('uuid')->willReturn($article->uuid()->toString());

        $articleRepository->expects(self::once())
            ->method('findByUuid')
            ->with($article->uuid()->toString())
            ->willReturn($article)
        ;

        $articleRepository->expects(self::once())
            ->method('changeStorageInformation')
            ->with($article)
        ;

        // Act
        $response = $useCase->execute($request);
        $articleUpdated = $response->article;

        // Assert
        self::assertSame(['colis', 1.0], $articleUpdated->packaging()->parcel());
        self::assertSame(['kilogramme', 6.0], $articleUpdated->packaging()->consumerUnit());
        self::assertSame(12.000, $articleUpdated->minStock());
        self::assertSame(25.0, $articleUpdated->quantity()->toFloat());
    }
}
