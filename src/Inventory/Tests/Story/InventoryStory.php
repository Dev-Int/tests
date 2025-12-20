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

namespace Inventory\Tests\Story;

use Admin\Tests\Factory\ArticleFactory;
use Admin\Tests\Factory\CompanyFactory;
use Admin\Tests\Factory\FamilyLogFactory;
use Admin\Tests\Factory\SupplierFactory;
use Admin\Tests\Factory\TaxFactory;
use Admin\Tests\Factory\UnitFactory;
use Admin\Tests\Factory\ZoneStorageFactory;
use Zenstruck\Foundry\Story;

/**
 * Story Inventory - Charge les fixtures Admin + Inventaires de test.
 *
 * Reproduit le comportement des DataFixtures pour créer un système complet
 */
final class InventoryStory extends Story
{
    public function build(): void
    {
        // === Admin Fixtures ===

        CompanyFactory::createOne([
            'name' => 'Dev-Int Création',
        ]);

        TaxFactory::createOne([
            'label' => 'Normale',
            'rate' => 20.00,
        ]);
        $taxReduite = TaxFactory::createOne([
            'label' => 'Réduite',
            'rate' => 5.50,
        ]);

        $unitKg = UnitFactory::createOne([
            'label' => 'Kilogramme',
            'symbol' => 'kg',
        ]);
        $unitLitre = UnitFactory::createOne([
            'label' => 'Litre',
            'symbol' => 'L',
        ]);

        $surgele = FamilyLogFactory::createOne([
            'label' => 'Surgelé',
            'parent' => null,
        ]);
        $frais = FamilyLogFactory::createOne([
            'label' => 'Frais',
            'parent' => null,
        ]);
        $epicerie = FamilyLogFactory::createOne([
            'label' => 'Épicerie',
            'parent' => null,
        ]);
        $fraisFruitsLegumes = FamilyLogFactory::createOne([
            'label' => 'Frais - Fruits & Légumes',
            'parent' => $frais->_real(),
        ]);

        ZoneStorageFactory::createOne([
            'label' => 'Réserve négative',
            'familyLog' => $surgele->_real(),
        ]);
        $zonePositive = ZoneStorageFactory::createOne([
            'label' => 'Réserve positive',
            'familyLog' => $frais->_real(),
        ]);
        ZoneStorageFactory::createOne([
            'label' => 'Réserve sèche',
            'familyLog' => $epicerie->_real(),
        ]);
        $zoneMaraichere = ZoneStorageFactory::createOne([
            'label' => 'Réserve maraîchère',
            'familyLog' => $fraisFruitsLegumes->_real(),
        ]);

        $supplier1 = SupplierFactory::createOne([
            'name' => 'Fournisseur 1',
        ]);
        $supplier2 = SupplierFactory::createOne([
            'name' => 'Fournisseur 2',
        ]);

        ArticleFactory::createOne([
            'label' => 'Tomates',
            'supplier' => $supplier1->_real(),
            'tax' => $taxReduite->_real(),
            'packaging' => [[$unitKg->_real()->toDomain(), 1.0], null, null],
            'zoneStorage' => $zoneMaraichere->_real(),
            'quantity' => 10.0, // 10 kg
        ]);
        ArticleFactory::createOne([
            'label' => 'Lait',
            'supplier' => $supplier2->_real(),
            'tax' => $taxReduite->_real(),
            'packaging' => [[$unitLitre->_real()->toDomain(), 1.0], null, null],
            'zoneStorage' => $zonePositive->_real(),
            'quantity' => 15.0, // 15 L
        ]);
    }
}
