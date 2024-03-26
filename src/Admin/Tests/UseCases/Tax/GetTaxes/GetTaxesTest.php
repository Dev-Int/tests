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

namespace Admin\Tests\UseCases\Tax\GetTaxes;

use Admin\Entities\Tax\TaxCollection;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Admin\UseCases\Gateway\TaxRepository;
use Admin\UseCases\Tax\GetTaxes\GetTaxes;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 */
final class GetTaxesTest extends TestCase
{
    public function testGetTaxesWithSuccess(): void
    {
        // Arrange
        $taxRepository = $this->createMock(TaxRepository::class);
        $useCase = new GetTaxes($taxRepository);
        $taxBuilder = new TaxDataBuilder();
        $tax1 = $taxBuilder->create('TVA taux normal', 20.0)->build();
        $tax2 = $taxBuilder->create('TVA taux réduit', 5.5)
            ->withUuid('d1e33989-ebba-4a4f-a897-13696e1a347c')
            ->build()
        ;
        $taxes = new TaxCollection();
        $taxes->add($tax1);
        $taxes->add($tax2);

        $taxRepository->expects(self::once())->method('findAllTaxes')->willReturn($taxes);

        // Act
        $response = $useCase->execute();
        $getTaxes = $response->taxes;

        // Assert
        self::assertCount(2, $getTaxes);
        $getTax1 = $getTaxes->current();
        self::assertSame('TVA taux normal', $getTax1->name()->toString());
        self::assertSame(0.2, $getTax1->rate());
        $getTaxes->next();
        $getTax2 = $getTaxes->current();
        self::assertSame('TVA taux réduit', $getTax2->name()->toString());
        self::assertSame(0.055, $getTax2->rate());
    }
}
