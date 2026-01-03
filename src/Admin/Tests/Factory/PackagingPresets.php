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

namespace Admin\Tests\Factory;

use Admin\Entities\Article\VO\Storage;
use Admin\Entities\Unit\Unit;

/**
 * Presets de packaging cohérents pour les fixtures et tests.
 *
 * Hiérarchie par taille croissante :
 * - Niveau 1 (ConsumerUnit) : kilogramme, litre, portion
 * - Niveau 2 (ConsumerUnit/SubPackage) : pièce, bouteille, poche
 * - Niveau 3 (SubPackage) : boîte
 * - Niveau 4 (Parcel) : carton, colis
 */
final class PackagingPresets
{
    // Slugs des unités (générés depuis les labels via slugify)
    private const string SLUG_KILOGRAMME = 'kilogramme';
    private const string SLUG_LITRE = 'litre';
    private const string SLUG_PORTION = 'portion';
    private const string SLUG_PIECE = 'piece';
    private const string SLUG_BOUTEILLE = 'bouteille';
    private const string SLUG_POCHE = 'poche';
    private const string SLUG_BOITE = 'boite';
    private const string SLUG_CARTON = 'carton';
    private const string SLUG_COLIS = 'colis';

    /**
     * Configuration des presets avec leurs caractéristiques.
     * Format: [consumerUnit, cuQty, subPackage, spQty, parcel, parcelQty].
     *
     * @var array<string, array{
     *     consumerUnit: string,
     *     cuQty: float,
     *     subPackage: string|null,
     *     spQty: float|null,
     *     parcel: string|null,
     *     parcelQty: float|null
     * }>
     */
    private const array PRESETS = [
        'bulk' => [
            'consumerUnit' => self::SLUG_KILOGRAMME,
            'cuQty' => 1.0,
            'subPackage' => null,
            'spQty' => null,
            'parcel' => self::SLUG_COLIS,
            'parcelQty' => 5.0,
        ],
        'liquid' => [
            'consumerUnit' => self::SLUG_LITRE,
            'cuQty' => 1.0,
            'subPackage' => self::SLUG_BOUTEILLE,
            'spQty' => 1.0,
            'parcel' => self::SLUG_CARTON,
            'parcelQty' => 6.0,
        ],
        'piece' => [
            'consumerUnit' => self::SLUG_PIECE,
            'cuQty' => 1.0,
            'subPackage' => self::SLUG_BOITE,
            'spQty' => 6.0,
            'parcel' => self::SLUG_CARTON,
            'parcelQty' => 4.0,
        ],
        'bottle' => [
            'consumerUnit' => self::SLUG_BOUTEILLE,
            'cuQty' => 1.0,
            'subPackage' => null,
            'spQty' => null,
            'parcel' => self::SLUG_CARTON,
            'parcelQty' => 12.0,
        ],
        'portion' => [
            'consumerUnit' => self::SLUG_PORTION,
            'cuQty' => 1.0,
            'subPackage' => self::SLUG_POCHE,
            'spQty' => 4.0,
            'parcel' => self::SLUG_CARTON,
            'parcelQty' => 10.0,
        ],
        'simple' => [
            'consumerUnit' => self::SLUG_PIECE,
            'cuQty' => 1.0,
            'subPackage' => null,
            'spQty' => null,
            'parcel' => null,
            'parcelQty' => null,
        ],
    ];

    /**
     * Type BULK : Produits en vrac (viande, fromage, légumes).
     * consumerUnit: kg → parcel: colis (5 kg/colis).
     *
     * @return array{array{Unit, float}, array{Unit, float}|null, array{Unit, float}|null}
     */
    public static function bulk(): array
    {
        return self::buildFromPreset('bulk');
    }

    /**
     * Type LIQUID : Liquides (lait, jus, huile).
     * consumerUnit: litre → subPackage: bouteille (1L) → parcel: carton (6 bouteilles).
     *
     * @return array{array{Unit, float}, array{Unit, float}|null, array{Unit, float}|null}
     */
    public static function liquid(): array
    {
        return self::buildFromPreset('liquid');
    }

    /**
     * Type PIECE : Produits à la pièce (yaourts, oeufs).
     * consumerUnit: pièce → subPackage: boîte (6 pièces) → parcel: carton (4 boîtes).
     *
     * @return array{array{Unit, float}, array{Unit, float}|null, array{Unit, float}|null}
     */
    public static function piece(): array
    {
        return self::buildFromPreset('piece');
    }

    /**
     * Type BOTTLE : Bouteilles (vin, bière).
     * consumerUnit: bouteille → parcel: carton (12 bouteilles).
     *
     * @return array{array{Unit, float}, array{Unit, float}|null, array{Unit, float}|null}
     */
    public static function bottle(): array
    {
        return self::buildFromPreset('bottle');
    }

    /**
     * Type PORTION : Portions préparées (plats, sauces).
     * consumerUnit: portion → subPackage: poche (4 portions) → parcel: carton (10 poches).
     *
     * @return array{array{Unit, float}, array{Unit, float}|null, array{Unit, float}|null}
     */
    public static function portion(): array
    {
        return self::buildFromPreset('portion');
    }

    /**
     * Type SIMPLE : Produit simple sans conditionnement.
     * consumerUnit: pièce uniquement.
     *
     * @return array{array{Unit, float}, array{Unit, float}|null, array{Unit, float}|null}
     */
    public static function simple(): array
    {
        return self::buildFromPreset('simple');
    }

    /**
     * Retourne un preset aléatoire.
     *
     * @return array{array{Unit, float}, array{Unit, float}|null, array{Unit, float}|null}
     */
    public static function random(): array
    {
        $presetNames = array_keys(self::PRESETS);
        $randomKey = $presetNames[array_rand($presetNames)];

        return self::buildFromPreset($randomKey);
    }

    /**
     * Retourne un preset adapté à la catégorie de produit (FamilyLog).
     *
     * @return array{array{Unit, float}, array{Unit, float}|null, array{Unit, float}|null}
     */
    public static function forCategory(string $familyLogSlug): array
    {
        return match (true) {
            str_contains($familyLogSlug, 'surgele'),
            str_contains($familyLogSlug, 'frais') => self::bulk(),

            str_contains($familyLogSlug, 'boisson'),
            str_contains($familyLogSlug, 'liquide') => self::liquid(),

            str_contains($familyLogSlug, 'laitier'),
            str_contains($familyLogSlug, 'dairy') => self::piece(),

            str_contains($familyLogSlug, 'vin'),
            str_contains($familyLogSlug, 'biere'),
            str_contains($familyLogSlug, 'alcool') => self::bottle(),

            str_contains($familyLogSlug, 'prepare'),
            str_contains($familyLogSlug, 'sauce') => self::portion(),

            default => self::random(),
        };
    }

    /**
     * Construit le format packaging attendu par ArticleFactory.
     *
     * @return array{array{Unit, float}, array{Unit, float}|null, array{Unit, float}|null}
     */
    private static function buildFromPreset(string $presetName): array
    {
        $preset = self::PRESETS[$presetName];

        $consumerUnit = self::getUnitBySlug($preset['consumerUnit']);

        $subPackage = null;
        $subPackageSlug = $preset['subPackage'];
        $subPackageQty = $preset['spQty'];
        if ($subPackageSlug !== null && $subPackageQty !== null) {
            $subPackageUnit = self::getUnitBySlug($subPackageSlug);
            $subPackage = [$subPackageUnit, $subPackageQty];
        }

        $parcel = null;
        $parcelSlug = $preset['parcel'];
        $parcelQty = $preset['parcelQty'];
        if ($parcelSlug !== null && $parcelQty !== null) {
            $parcelUnit = self::getUnitBySlug($parcelSlug);
            $parcel = [$parcelUnit, $parcelQty];
        }

        return [
            [$consumerUnit, $preset['cuQty']],
            $subPackage,
            $parcel,
        ];
    }

    /**
     * Récupère ou crée une Unit par son slug.
     */
    private static function getUnitBySlug(string $slug): Unit
    {
        $unitProxy = UnitFactory::repository()->findOneBy(['slug' => $slug]);

        if ($unitProxy === null) {
            // Créer l'unité si elle n'existe pas
            $label = self::getLabelFromSlug($slug);
            $abbreviation = self::getAbbreviationFromSlug($slug);
            $unitProxy = UnitFactory::createOne([
                'label' => $label,
                'abbreviation' => $abbreviation,
            ]);
        }

        return $unitProxy->_real()->toDomain();
    }

    /**
     * Convertit un slug en label (avec accents).
     */
    private static function getLabelFromSlug(string $slug): string
    {
        return match ($slug) {
            self::SLUG_PIECE => Storage::UNIT_PIECE,
            self::SLUG_BOITE => Storage::UNIT_BOITE,
            self::SLUG_KILOGRAMME => Storage::UNIT_KILOGRAMME,
            self::SLUG_LITRE => Storage::UNIT_LITRE,
            self::SLUG_BOUTEILLE => Storage::UNIT_BOUTEILLE,
            self::SLUG_POCHE => Storage::UNIT_POCHE,
            self::SLUG_PORTION => Storage::UNIT_PORTION,
            self::SLUG_CARTON => Storage::UNIT_CARTON,
            self::SLUG_COLIS => Storage::UNIT_COLIS,
            default => $slug,
        };
    }

    /**
     * Retourne l'abréviation standard pour un slug.
     */
    private static function getAbbreviationFromSlug(string $slug): string
    {
        return match ($slug) {
            self::SLUG_KILOGRAMME => 'kg',
            self::SLUG_LITRE => 'l',
            self::SLUG_PIECE => 'pce',
            self::SLUG_BOUTEILLE => 'btl',
            self::SLUG_BOITE => 'bte',
            self::SLUG_POCHE => 'pch',
            self::SLUG_PORTION => 'ptn',
            self::SLUG_CARTON => 'ctn',
            self::SLUG_COLIS => 'cls',
            default => substr($slug, 0, 3),
        };
    }
}
