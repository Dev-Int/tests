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

use Admin\Entities\Repository\ArticleRepository;
use Admin\Tests\DataBuilder\ArticleDataBuilder;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use Admin\Tests\DataBuilder\ZoneStorageDataBuilder;
use Admin\UseCases\Article\ChangeStorageInformation\ChangeArticleStorageInformation;
use Admin\UseCases\Article\ChangeStorageInformation\ChangeArticleStorageInformationRequest;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 */
final class ChangeArticleStorageInformationTest extends TestCase
{
    public function testChangeStorageInformationWithSuccess(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $articleRepository = $this->createMock(ArticleRepository::class);
        $useCase = new ChangeArticleStorageInformation($articleRepository);
        $request = $this->createMock(ChangeArticleStorageInformationRequest::class);
        $frais = (new FamilyLogDataBuilder())->create('Frais')->build();
        $fraisViande = (new FamilyLogDataBuilder())->create('Viande')
            ->withUuid($faker->uuid())
            ->withParent($frais)
            ->build()
        ;
        $supplier1 = (new SupplierDataBuilder())->create('Supplier 1', $frais)->build();
        $storageFrais = (new ZoneStorageDataBuilder())->create('Réserve positive', $frais)->build();
        $tax = (new TaxDataBuilder())->create('TVA taux réduit', 5.5)->build();
        $colis = (new UnitDataBuilder())->create('Colis', 'cls')->build();
        $kilogramme = (new UnitDataBuilder())->create('Kilogramme', 'kg')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $article = (new ArticleDataBuilder())
            ->create(
                'Jambon Trad 6kg',
                $supplier1,
                $tax,
                [$storageFrais],
                $fraisViande,
                [[$colis, 1.0], null, null]
            )
            ->build()
        ;

        $request->expects(self::once())->method('packaging')->willReturn([[$colis, 1], null, [$kilogramme, 6.000]]);
        $request->expects(self::once())->method('minStock')->willReturn(12.000);
        $request->expects(self::once())->method('uuid')->willReturn($article->uuid()->toString());

        $articleRepository->expects(self::once())
            ->method('getByUuid')
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
        self::assertSame($colis, $articleUpdated->packaging()->parcel()[0]);
        self::assertSame(1.0, $articleUpdated->packaging()->parcel()[1]);
        self::assertNotNull($articleUpdated->packaging()->consumerUnit());
        self::assertSame($kilogramme, $articleUpdated->packaging()->consumerUnit()[0]);
        self::assertSame(6.000, $articleUpdated->packaging()->consumerUnit()[1]);
        self::assertSame(12.000, $articleUpdated->minStock());
        self::assertSame(12.5, $articleUpdated->quantity()->toFloat());
    }
}
