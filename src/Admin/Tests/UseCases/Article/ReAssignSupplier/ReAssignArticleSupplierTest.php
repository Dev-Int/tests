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

namespace Admin\Tests\UseCases\Article\ReAssignSupplier;

use Admin\Entities\Exception\FamilyLog\BadFamilyLogAssigned;
use Admin\Entities\FamilyLog\FamilyLog;
use Admin\Entities\Repository\ArticleRepository;
use Admin\Tests\DataBuilder\ArticleDataBuilder;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use Admin\Tests\DataBuilder\ZoneStorageDataBuilder;
use Admin\UseCases\Article\ReAssignSupplier\ReAssignArticleSupplier;
use Admin\UseCases\Article\ReAssignSupplier\ReAssignArticleSupplierRequest;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 */
final class ReAssignArticleSupplierTest extends TestCase
{
    public function testAssignArticleSupplierWithSuccess(): void
    {
        // Arrange
        $articleRepository = $this->createMock(ArticleRepository::class);
        $useCase = new ReAssignArticleSupplier($articleRepository);
        $request = $this->createMock(ReAssignArticleSupplierRequest::class);
        $frais = (new FamilyLogDataBuilder())->create('Frais')->build();
        $surgele = (new FamilyLogDataBuilder())->create('Surgelé')
            ->withUuid('1454df78-226c-46ef-9e1f-aaaf93d739c4')
            ->build()
        ;
        $fraisViande = (new FamilyLogDataBuilder())->create('Viande')
            ->withUuid('46835a0c-3e6c-4a5c-ab80-b1d6d96b05ae')
            ->withParent($frais)
            ->build()
        ;
        $surgeleViande = (new FamilyLogDataBuilder())->create('Viande')
            ->withUuid('7da2f3bb-9cca-4983-b476-a76ba5e0e3f4')
            ->withParent($surgele)
            ->build()
        ;
        $supplier1 = (new SupplierDataBuilder())->create('Supplier 1', $frais)->build();
        $supplier2 = (new SupplierDataBuilder())->create('Supplier 2', $surgele)
            ->withUuid('aa19a993-f828-484c-94e9-44788054412e')
            ->build()
        ;
        $storageFrais = (new ZoneStorageDataBuilder())->create('Réserve positive', $frais)->build();
        $storageSurgele = (new ZoneStorageDataBuilder())->create('Réserve négative', $surgele)->build();
        $tax = (new TaxDataBuilder())->create('TVA taux réduit', 5.5)->build();
        $unit = (new UnitDataBuilder())->create('Colis', 'cls')->build();
        $article = (new ArticleDataBuilder())
            ->create(
                'Jambon Trad 6kg',
                $supplier1,
                $tax,
                [$storageFrais],
                $fraisViande,
                [[$unit, 1.0], null, null]
            )
            ->build()
        ;

        $request->expects(self::exactly(2))->method('supplier')->willReturn($supplier2);
        $request->expects(self::exactly(2))->method('familyLog')->willReturn($surgeleViande);
        $request->expects(self::exactly(2))->method('zoneStorages')->willReturn([$storageSurgele]);
        $request->expects(self::once())->method('uuid')->willReturn($article->uuid());

        $articleRepository->expects(self::once())
            ->method('getByUuid')
            ->with($article->uuid())
            ->willReturn($article)
        ;

        $articleRepository->expects(self::once())
            ->method('reAssignSupplier')
            ->with($article)
        ;

        // Act
        $response = $useCase->execute($request);
        $articleUpdated = $response->article;

        // Assert
        self::assertSame('Supplier 2', $articleUpdated->supplier()->name()->toString());
        self::assertCount(1, $articleUpdated->zoneStorages()->toArray());
        self::assertSame('reserve-negative', $articleUpdated->zoneStorages()->current()->slug());
        self::assertSame('Viande', $articleUpdated->familyLog()->label()->toString());
        $parent = $articleUpdated->familyLog()->parent();
        self::assertInstanceOf(FamilyLog::class, $parent);
        self::assertSame('Surgelé', $parent->label()->toString());
    }

    public function testAssignArticleSupplierFailWithBadFamilyLogAssignedException(): void
    {
        // Arrange
        $articleRepository = $this->createMock(ArticleRepository::class);
        $useCase = new ReAssignArticleSupplier($articleRepository);
        $request = $this->createMock(ReAssignArticleSupplierRequest::class);
        $frais = (new FamilyLogDataBuilder())->create('Frais')->build();
        $surgele = (new FamilyLogDataBuilder())
            ->create('Surgelé')
            ->withUuid('1454df78-226c-46ef-9e1f-aaaf93d739c4')
            ->build()
        ;
        $fraisViande = (new FamilyLogDataBuilder())
            ->create('Viande')
            ->withUuid('46835a0c-3e6c-4a5c-ab80-b1d6d96b05ae')
            ->withParent($frais)
            ->build()
        ;
        $supplier1 = (new SupplierDataBuilder())->create('Supplier 1', $frais)->build();
        $supplier2 = (new SupplierDataBuilder())
            ->create('Supplier 2', $surgele)
            ->withUuid('aa19a993-f828-484c-94e9-44788054412e')
            ->build()
        ;
        $storageFrais = (new ZoneStorageDataBuilder())->create('Réserve positive', $frais)->build();
        $storageSurgele = (new ZoneStorageDataBuilder())
            ->create('Réserve négative', $surgele)
            ->withUuid('fd8c9618-9a4f-40d8-a331-480a0448da10')
            ->build()
        ;
        $tax = (new TaxDataBuilder())->create('TVA taux réduit', 5.5)->build();
        $unit = (new UnitDataBuilder())->create('Colis', 'cls')->build();
        $article = (new ArticleDataBuilder())
            ->create(
                'Jambon Trad 6kg',
                $supplier1,
                $tax,
                [$storageFrais],
                $fraisViande,
                [[$unit, 1.0], null, null]
            )
            ->build()
        ;

        $request->expects(self::once())->method('supplier')->willReturn($supplier2);
        $request->expects(self::exactly(2))->method('familyLog')->willReturn($fraisViande);
        $request->expects(self::once())->method('zoneStorages')->willReturn([$storageFrais, $storageSurgele]);
        $request->expects(self::never())->method('uuid')->willReturn($article->uuid());

        $articleRepository->expects(self::never())
            ->method('getByUuid')
            ->with($article->uuid())
            ->willReturn($article)
        ;

        $articleRepository->expects(self::never())
            ->method('reAssignSupplier')
            ->with($article)
        ;

        // Act && Assert
        $this->expectException(BadFamilyLogAssigned::class);
        $this->expectExceptionMessage(BadFamilyLogAssigned::MESSAGE);
        $useCase->execute($request);
    }
}
