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

namespace Admin\Tests\UseCases\Article\CreateArticle;

use Admin\Entities\Exception\Article\ArticleAlreadyExistsException;
use Admin\Entities\Exception\FamilyLog\BadFamilyLogAssigned;
use Admin\Entities\FamilyLog\FamilyLog;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use Admin\Tests\DataBuilder\ZoneStorageDataBuilder;
use Admin\UseCases\Article\CreateArticle\CreateArticle;
use Admin\UseCases\Article\CreateArticle\CreateArticleRequest;
use Admin\UseCases\Gateway\ArticleRepository;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 */
final class CreateArticleTest extends TestCase
{
    public function testCreateArticleWithSuccess(): void
    {
        // Arrange
        $articleRepository = $this->createMock(ArticleRepository::class);
        $useCase = new CreateArticle($articleRepository);
        $request = $this->createMock(CreateArticleRequest::class);
        $familyLogGrandParent = (new FamilyLogDataBuilder())->create('Alimentaire')->build();
        $familyLogParent = (new FamilyLogDataBuilder())->create('Frais')
            ->withUuid('b99a4348-8f8b-42b1-acde-89a77b3e026d')
            ->withParent($familyLogGrandParent)
            ->build()
        ;
        $familyLog = (new FamilyLogDataBuilder())
            ->create('Viande')
            ->withUuid('46835a0c-3e6c-4a5c-ab80-b1d6d96b05ae')
            ->withParent($familyLogParent)
            ->build()
        ;
        $supplier = (new SupplierDataBuilder())->create('Supplier 1', $familyLogGrandParent)->build();
        $zoneStorage = (new ZoneStorageDataBuilder())->create('Réserve froide', $familyLogParent)->build();
        $tax = (new TaxDataBuilder())->create('TVA taux réduit', 5.5)->build();
        $unit = (new UnitDataBuilder())->create('Colis', 'cls')->build();

        $request->expects(self::exactly(2))->method('name')->willReturn('Jambon Trad 6kg');
        $request->expects(self::exactly(2))->method('supplier')->willReturn($supplier);
        $request->expects(self::once())->method('packaging')->willReturn([[$unit, 1.0], null, null]);
        $request->expects(self::once())->method('unitPrice')->willReturn(2550);
        $request->expects(self::once())->method('tax')->willReturn($tax);
        $request->expects(self::once())->method('minStock')->willReturn(8.000);
        $request->expects(self::once())->method('quantity')->willReturn(null);
        $request->expects(self::exactly(2))->method('zoneStorages')->willReturn([$zoneStorage]);
        $request->expects(self::exactly(2))->method('familyLog')->willReturn($familyLog);

        $articleRepository->expects(self::once())
            ->method('isExists')
            ->with('Jambon Trad 6kg')
            ->willReturn(false)
        ;

        $articleRepository->expects(self::once())
            ->method('save')
        ;

        // Act
        $response = $useCase->execute($request);
        $article = $response->article;

        // Assert
        self::assertSame('Jambon Trad 6kg', $article->name()->toString());
        self::assertSame('Supplier 1', $article->supplier()->name()->toString());
        self::assertSame([$unit, 1.0], $article->packaging()->parcel());
        self::assertSame(25.50, $article->unitPrice()->toFloat());
        self::assertSame(2550, $article->unitPrice()->toInt());
        self::assertSame(0.055, $article->tax()->rate());
        self::assertSame('TVA taux réduit', $article->tax()->name()->toString());
        self::assertSame(8.000, $article->minStock());
        self::assertSame('reserve-froide', $article->zoneStorages()->current()->slug());
        self::assertSame('Viande', $article->familyLog()->label()->toString());
        $parent = $article->familyLog()->parent();
        self::assertInstanceOf(FamilyLog::class, $parent);
        self::assertSame('Frais', $parent->label()->toString());
        self::assertSame(0.0, $article->quantity()->toFloat());
        self::assertTrue($article->active());
    }

    public function testCreateArticleFailWithBadFamilyLogAssignedException(): void
    {
        // Arrange
        $articleRepository = $this->createMock(ArticleRepository::class);
        $useCase = new CreateArticle($articleRepository);
        $request = $this->createMock(CreateArticleRequest::class);
        $frais = (new FamilyLogDataBuilder())->create('Frais')->build();
        $surgele = (new FamilyLogDataBuilder())
            ->create('Surgelé')
            ->withUuid('aa19a993-f828-484c-94e9-44788054412e')
            ->build()
        ;
        $fraisViande = (new FamilyLogDataBuilder())
            ->create('Viande')
            ->withUuid('46835a0c-3e6c-4a5c-ab80-b1d6d96b05ae')
            ->withParent($frais)
            ->build()
        ;
        $supplier = (new SupplierDataBuilder())->create('Supplier 1', $frais)->build();
        $storageFrais = (new ZoneStorageDataBuilder())->create('Réserve positive', $frais)->build();
        $storageSurgele = (new ZoneStorageDataBuilder())
            ->create('Réserve négative', $surgele)
            ->withUuid('fd8c9618-9a4f-40d8-a331-480a0448da10')
            ->build()
        ;
        $tax = (new TaxDataBuilder())->create('TVA taux réduit', 5.5)->build();
        $unit = (new UnitDataBuilder())->create('Colis', 'cls')->build();

        $request->expects(self::once())->method('name')->willReturn('Jambon Trad 6kg');
        $request->expects(self::once())->method('supplier')->willReturn($supplier);
        $request->expects(self::never())->method('packaging')->willReturn([[$unit, 1.0], null, null]);
        $request->expects(self::never())->method('unitPrice')->willReturn(2550);
        $request->expects(self::never())->method('tax')->willReturn($tax);
        $request->expects(self::never())->method('minStock')->willReturn(8.000);
        $request->expects(self::never())->method('quantity')->willReturn(null);
        $request->expects(self::once())->method('zoneStorages')->willReturn([$storageFrais, $storageSurgele]);
        $request->expects(self::once())->method('familyLog')->willReturn($fraisViande);

        $articleRepository->expects(self::once())
            ->method('isExists')
            ->with('Jambon Trad 6kg')
            ->willReturn(false)
        ;

        $articleRepository->expects(self::never())
            ->method('save')
        ;

        // Act && Assert
        $this->expectException(BadFamilyLogAssigned::class);
        $this->expectExceptionMessage(BadFamilyLogAssigned::MESSAGE);
        $useCase->execute($request);
    }

    public function testCreateArticleFailWithAlreadyExistsException(): void
    {
        // Arrange
        $articleRepository = $this->createMock(ArticleRepository::class);
        $useCase = new CreateArticle($articleRepository);
        $request = $this->createMock(CreateArticleRequest::class);
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

        $request->expects(self::exactly(2))->method('name')->willReturn('Jambon Trad 6kg');
        $request->expects(self::never())->method('supplier')->willReturn($supplier);
        $request->expects(self::never())->method('packaging')->willReturn([[$unit, 1.0], null, null]);
        $request->expects(self::never())->method('unitPrice')->willReturn(2550);
        $request->expects(self::never())->method('tax')->willReturn($tax);
        $request->expects(self::never())->method('minStock')->willReturn(8.000);
        $request->expects(self::never())->method('quantity')->willReturn(null);
        $request->expects(self::never())->method('zoneStorages')->willReturn([$zoneStorage]);
        $request->expects(self::never())->method('familyLog')->willReturn($familyLog);

        $articleRepository->expects(self::once())
            ->method('isExists')
            ->with('Jambon Trad 6kg')
            ->willReturn(true)
        ;

        $articleRepository->expects(self::never())
            ->method('save')
        ;

        // Act && Assert
        $this->expectException(ArticleAlreadyExistsException::class);
        $this->expectExceptionMessage(ArticleAlreadyExistsException::MESSAGE);
        $useCase->execute($request);
    }
}
