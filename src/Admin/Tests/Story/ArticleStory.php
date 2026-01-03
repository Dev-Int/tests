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

namespace Admin\Tests\Story;

use Admin\Tests\Factory\ArticleFactory;
use Admin\Tests\Factory\CompanyFactory;
use Admin\Tests\Factory\FamilyLogFactory;
use Admin\Tests\Factory\PackagingPresets;
use Admin\Tests\Factory\SupplierFactory;
use Admin\Tests\Factory\TaxFactory;
use Admin\Tests\Factory\UnitFactory;
use Admin\Tests\Factory\ZoneStorageFactory;
use Zenstruck\Foundry\Story;

final class ArticleStory extends Story
{
    public function build(): void
    {
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

        UnitFactory::createOne([
            'label' => 'kilogramme',
            'abbreviation' => 'kg',
        ]);
        UnitFactory::createOne([
            'label' => 'litre',
            'abbreviation' => 'l',
        ]);
        UnitFactory::createOne([
            'label' => 'pièce',
            'abbreviation' => 'pce',
        ]);
        UnitFactory::createOne([
            'label' => 'bouteille',
            'abbreviation' => 'btl',
        ]);
        UnitFactory::createOne([
            'label' => 'boîte',
            'abbreviation' => 'bte',
        ]);
        UnitFactory::createOne([
            'label' => 'carton',
            'abbreviation' => 'ctn',
        ]);
        UnitFactory::createOne([
            'label' => 'colis',
            'abbreviation' => 'cls',
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

        // Articles avec packagings cohérents
        ArticleFactory::createOne([
            'name' => 'Tomates',
            'supplier' => $supplier1->_real(),
            'tax' => $taxReduite->_real(),
            'packaging' => PackagingPresets::bulk(), // kg → colis (5 kg)
            'zoneStorages' => [$zoneMaraichere->_real()],
            'familyLog' => $fraisFruitsLegumes->_real(),
            'quantity' => 10.0, // 10 kg
        ]);
        ArticleFactory::createOne([
            'name' => 'Carottes',
            'supplier' => $supplier1->_real(),
            'tax' => $taxReduite->_real(),
            'packaging' => PackagingPresets::bulk(), // kg → colis (5 kg)
            'zoneStorages' => [$zoneMaraichere->_real()],
            'familyLog' => $fraisFruitsLegumes->_real(),
            'quantity' => 5.0, // 5 kg
        ]);
        ArticleFactory::createOne([
            'name' => 'Lait',
            'supplier' => $supplier2->_real(),
            'tax' => $taxReduite->_real(),
            'packaging' => PackagingPresets::liquid(), // litre → bouteille → carton (6 btl)
            'zoneStorages' => [$zonePositive->_real()],
            'familyLog' => $frais->_real(),
            'quantity' => 15.0, // 15 L
        ]);
        ArticleFactory::createOne([
            'name' => 'Yaourt',
            'supplier' => $supplier1->_real(),
            'tax' => $taxReduite->_real(),
            'packaging' => PackagingPresets::piece(), // pièce → boîte (6) → carton (4)
            'zoneStorages' => [$zonePositive->_real()],
            'familyLog' => $frais->_real(),
            'quantity' => 20.0, // 20 pots
        ]);
    }
}
