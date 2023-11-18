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

namespace Article\Tests\UseCases\CreateArticle;

use Article\Tests\DataBuilder\FamilyLogDataBuilder;
use Article\Tests\DataBuilder\SupplierDataBuilder;
use Article\Tests\DataBuilder\ZoneStorageDataBuilder;
use Article\UseCases\CreateArticle\CreateArticle;
use Article\UseCases\CreateArticle\CreateArticleRequest;
use Article\UseCases\Gateway\ArticleRepository;
use Article\UseCases\Gateway\FamilyLogRepository;
use Article\UseCases\Gateway\SupplierRepository;
use Article\UseCases\Gateway\ZoneStorageRepository;
use PHPUnit\Framework\TestCase;
use Shared\Entities\VO\FamilyLog;

/**
 * @group unitTest
 */
final class CreateArticleTest extends TestCase
{
    public function testCreateArticleWithSuccess(): void
    {
        // Arrange
        $articleRepository = $this->createMock(ArticleRepository::class);
        $supplierRepository = $this->createMock(SupplierRepository::class);
        $zoneStorageRepository = $this->createMock(ZoneStorageRepository::class);
        $familyLogRepository = $this->createMock(FamilyLogRepository::class);
        $useCase = new CreateArticle(
            $articleRepository,
            $supplierRepository,
            $zoneStorageRepository,
            $familyLogRepository
        );
        $request = $this->createMock(CreateArticleRequest::class);

        $supplier = (new SupplierDataBuilder())->build();
        $zoneStorage = (new ZoneStorageDataBuilder('Réserve positive'))->build();
        $familyLog = (new FamilyLogDataBuilder('Viande'))->withParent('Frais')->build();

        $request->expects(self::exactly(2))->method('name')->willReturn('Jambon Trad 6kg');
        $request->expects(self::once())->method('supplierId')->willReturn(SupplierDataBuilder::VALID_UUID);
        $request->expects(self::once())->method('packaging')->willReturn([['Colis', 1], null, null]);
        $request->expects(self::once())->method('price')->willReturn(25.50);
        $request->expects(self::once())->method('taxes')->willReturn(5.5);
        $request->expects(self::once())->method('minStock')->willReturn(8.000);
        $request->expects(self::once())->method('zoneStorageIds')->willReturn(['Réserve positive']);
        $request->expects(self::once())->method('familyLogName')->willReturn('Viande');
        $request->expects(self::once())->method('quantity')->willReturn(12.250);

        $articleRepository->expects(self::once())
            ->method('articleExists')
            ->with('Jambon Trad 6kg')
            ->willReturn(false)
        ;
        $supplierRepository->expects(self::once())
            ->method('findById')
            ->with(SupplierDataBuilder::VALID_UUID)
            ->willReturn($supplier)
        ;
        $zoneStorageRepository->expects(self::once())
            ->method('findByIds')
            ->with(['Réserve positive'])
            ->willReturn([$zoneStorage])
        ;
        $familyLogRepository->expects(self::once())
            ->method('findById')
            ->with('Viande')
            ->willReturn($familyLog)
        ;

        // Act
        $article = $useCase->__invoke($request);

        // Assert
        self::assertSame('Jambon Trad 6kg', $article->name()->toString());
        self::assertSame('Davigel', $article->supplier()->name()->toString());
        self::assertSame(['colis', 1.0], $article->packaging()->parcel());
        self::assertSame(25.50, $article->price()->toFloat());
        self::assertSame(2550, $article->price()->toInt());
        self::assertSame(0.055, $article->taxes()->rate());
        self::assertSame("5,50\u{a0}%", $article->taxes()->name());
        self::assertSame(8.000, $article->minStock());
        self::assertSame('reserve-positive', $article->zoneStorages()->current()->slug());
        self::assertSame('Viande', $article->familyLog()->name()->toString());
        $parent = $article->familyLog()->parent();
        self::assertInstanceOf(FamilyLog::class, $parent);
        self::assertSame('Frais', $parent->name()->toString());
        self::assertSame(12.250, $article->quantity()->toFloat());
    }
}
