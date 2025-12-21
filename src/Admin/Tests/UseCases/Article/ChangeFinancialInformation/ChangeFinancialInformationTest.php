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

namespace Admin\Tests\UseCases\Article\ChangeFinancialInformation;

use Admin\Entities\Repository\ArticleRepository;
use Admin\Tests\DataBuilder\ArticleDataBuilder;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use Admin\Tests\DataBuilder\ZoneStorageDataBuilder;
use Admin\UseCases\Article\ChangeFinancialInformation\ChangeArticleFinancialInformation;
use Admin\UseCases\Article\ChangeFinancialInformation\ChangeArticleFinancialInformationRequest;
use PHPUnit\Framework\TestCase;

final class ChangeFinancialInformationTest extends TestCase
{
    public function testChangeFinancialInformationWithSuccess(): void
    {
        // Arrange
        $articleRepository = $this->createMock(ArticleRepository::class);
        $useCase = new ChangeArticleFinancialInformation($articleRepository);
        $request = $this->createMock(ChangeArticleFinancialInformationRequest::class);
        $frais = (new FamilyLogDataBuilder())->create('Frais')->build();
        $fraisViande = (new FamilyLogDataBuilder())->create('Viande')
            ->withUuid('46835a0c-3e6c-4a5c-ab80-b1d6d96b05ae')
            ->withParent($frais)
            ->build()
        ;
        $supplier1 = (new SupplierDataBuilder())->create('Supplier 1', $frais)->build();
        $storageFrais = (new ZoneStorageDataBuilder())->create('Réserve positive', $frais)->build();
        $tax20 = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $tax55 = (new TaxDataBuilder())->create('TVA taux réduit', 5.5)
            ->withUuid('69da1c23-304b-47d8-be43-8e41f7bdfa75')
            ->build()
        ;
        $colis = (new UnitDataBuilder())->create('Colis', 'cls')->build();
        $kilogramme = (new UnitDataBuilder())->create('Kilogramme', 'kg')
            ->withUuid('2a1882c5-fbe9-4259-9637-47cc4d6c5508')
            ->build()
        ;
        $article = (new ArticleDataBuilder())
            ->create(
                'Jambon Trad 6kg',
                $supplier1,
                $tax20,
                [$storageFrais],
                $fraisViande,
                [[$colis, 1.0], null, [$kilogramme, 6.0]]
            )
            ->build()
        ;
        $request->expects(self::once())->method('amount')->willReturn(725);
        $request->expects(self::once())->method('tax')->willReturn($tax55);
        $request->expects(self::once())->method('uuid')->willReturn($article->uuid());

        $articleRepository->expects(self::once())
            ->method('getByUuid')
            ->with($article->uuid())
            ->willReturn($article)
        ;

        $articleRepository->expects(self::once())
            ->method('changeFinancialInformation')
            ->with($article)
        ;

        // Act
        $response = $useCase->execute($request);
        $articleUpdated = $response->article;

        // Assert
        self::assertSame(725, $articleUpdated->unitPrice()->toInt());
        self::assertSame(7.25, $articleUpdated->unitPrice()->toFloat());
        self::assertEquals($tax55, $articleUpdated->tax());
    }
}
