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

use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineZoneStorageRepository;
use Admin\Entities\FamilyLog\FamilyLog;
use Admin\Entities\ZoneStorage\ZoneStorage;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\ZoneStorageDataBuilder;
use Faker\Factory;

final readonly class FamilyLogConfigurationStory
{
    public function __construct(
        private DoctrineFamilyLogRepository $familyLogRepository,
        private DoctrineZoneStorageRepository $zoneStorageRepository,
    ) {
    }

    /**
     * Crée une FamilyLog simple + sa ZoneStorage.
     *
     * @return array{familyLog: FamilyLog, zoneStorage: ZoneStorage}
     */
    public function createFamilyLogWithZone(
        string $familyLogLabel = 'Produits frais',
        string $zoneLabel = 'Chambre froide',
    ): array {
        $faker = Factory::create('fr_FR');

        $familyLog = (new FamilyLogDataBuilder())
            ->create(label: $familyLogLabel)
            ->withUuid($faker->uuid())
            ->build()
        ;
        $this->familyLogRepository->save($familyLog);

        $zoneStorage = (new ZoneStorageDataBuilder())
            ->create(label: $zoneLabel, familyLog: $familyLog)
            ->withUuid($faker->uuid())
            ->build()
        ;
        $this->zoneStorageRepository->save($zoneStorage);

        return [
            'familyLog' => $familyLog,
            'zoneStorage' => $zoneStorage,
        ];
    }

    /**
     * Crée une hiérarchie de FamilyLog (parent + enfant).
     *
     * @return array{parent: FamilyLog, child: FamilyLog}
     */
    public function createFamilyLogHierarchy(
        string $parentLabel = 'Épicerie',
        string $childLabel = 'Épicerie salée',
    ): array {
        $faker = Factory::create('fr_FR');

        $parent = (new FamilyLogDataBuilder())
            ->create($parentLabel)
            ->withUuid($faker->uuid())
            ->build()
        ;
        $this->familyLogRepository->save($parent);

        $child = (new FamilyLogDataBuilder())
            ->create($childLabel)
            ->withUuid($faker->uuid())
            ->withParent($parent)
            ->build()
        ;
        $this->familyLogRepository->save($child);

        return [
            'parent' => $parent,
            'child' => $child,
        ];
    }

    /**
     * Crée plusieurs ZoneStorage pour une même FamilyLog.
     *
     * @param array<string> $zoneLabels
     *
     * @return array<ZoneStorage>
     */
    public function createMultipleZoneStoragesForFamily(
        FamilyLog $familyLog,
        array $zoneLabels = ['Réserve froide', 'Réserve négative', 'Réserve sèche'],
    ): array {
        $faker = Factory::create('fr_FR');
        $zones = [];

        foreach ($zoneLabels as $label) {
            $zone = (new ZoneStorageDataBuilder())
                ->create(label: $label, familyLog: $familyLog)
                ->withUuid($faker->uuid())
                ->build()
            ;
            $this->zoneStorageRepository->save($zone);
            $zones[] = $zone;
        }

        return $zones;
    }

    /**
     * Crée une hiérarchie FamilyLog à trois niveaux (grand-parent → parent → enfant).
     *
     * @return array{grandParent: FamilyLog, parent: FamilyLog, child: FamilyLog}
     */
    public function createThreeLevelFamilyLogHierarchy(
        string $grandParentLabel = 'Alimentaire',
        string $parentLabel = 'Frais',
        string $childLabel = 'Viande',
    ): array {
        $faker = Factory::create('fr_FR');

        $grandParent = (new FamilyLogDataBuilder())
            ->create($grandParentLabel)
            ->withUuid($faker->uuid())
            ->build()
        ;
        $this->familyLogRepository->save($grandParent);

        $parent = (new FamilyLogDataBuilder())
            ->create($parentLabel)
            ->withUuid($faker->uuid())
            ->withParent($grandParent)
            ->build()
        ;
        $this->familyLogRepository->save($parent);

        $child = (new FamilyLogDataBuilder())
            ->create($childLabel)
            ->withUuid($faker->uuid())
            ->withParent($parent)
            ->build()
        ;
        $this->familyLogRepository->save($child);

        return [
            'grandParent' => $grandParent,
            'parent' => $parent,
            'child' => $child,
        ];
    }

    /**
     * Crée une ZoneStorage pour une FamilyLog existante.
     */
    public function createZoneStorageForFamilyLog(
        FamilyLog $familyLog,
        string $zoneLabel = 'Réserve froide',
    ): ZoneStorage {
        $faker = Factory::create('fr_FR');

        $zoneStorage = (new ZoneStorageDataBuilder())
            ->create(label: $zoneLabel, familyLog: $familyLog)
            ->withUuid($faker->uuid())
            ->build()
        ;
        $this->zoneStorageRepository->save($zoneStorage);

        return $zoneStorage;
    }

    /**
     * Crée la hiérarchie complète de FamilyLog basée sur les fixtures (11 entités).
     *
     * Arbre Alimentaire :
     * - Alimentaire
     *   - Surgelé
     *     - Viande
     *     - Fruits & Légumes
     *   - Frais
     *     - Viande
     *     - Fruits & Légumes
     *   - Épicerie
     *
     * Arbre Non-alimentaire:
     * - Non-alimentaire
     *   - Emballage
     *   - Hygiène
     *
     * @return array{
     *     alimentaire: FamilyLog,
     *     surgele: FamilyLog,
     *     surgeleViande: FamilyLog,
     *     surgeleFruitsLegumes: FamilyLog,
     *     frais: FamilyLog,
     *     fraisViande: FamilyLog,
     *     fraisFruitsLegumes: FamilyLog,
     *     epicerie: FamilyLog,
     *     nonAlimentaire: FamilyLog,
     *     emballage: FamilyLog,
     *     hygiene: FamilyLog
     * }
     */
    public function createCompleteFamilyLogHierarchy(): array
    {
        // Niveau 1 : Racines
        $alimentaire = (new FamilyLogDataBuilder())
            ->create('Alimentaire')
            ->withUuid(FamilyLogDataBuilder::ALIMENTAIRE_UUID)
            ->build()
        ;
        $this->familyLogRepository->save($alimentaire);

        $nonAlimentaire = (new FamilyLogDataBuilder())
            ->create('Non-alimentaire')
            ->withUuid(FamilyLogDataBuilder::NON_ALIMENTAIRE_UUID)
            ->build()
        ;
        $this->familyLogRepository->save($nonAlimentaire);

        // Niveau 2 : Enfants de Alimentaire
        $surgele = (new FamilyLogDataBuilder())
            ->create('Surgelé')
            ->withUuid(FamilyLogDataBuilder::SURGELE_UUID)
            ->withParent($alimentaire)
            ->build()
        ;
        $this->familyLogRepository->save($surgele);

        $frais = (new FamilyLogDataBuilder())
            ->create('Frais')
            ->withUuid(FamilyLogDataBuilder::FRAIS_UUID)
            ->withParent($alimentaire)
            ->build()
        ;
        $this->familyLogRepository->save($frais);

        $epicerie = (new FamilyLogDataBuilder())
            ->create('Épicerie')
            ->withUuid(FamilyLogDataBuilder::EPICERIE_UUID)
            ->withParent($alimentaire)
            ->build()
        ;
        $this->familyLogRepository->save($epicerie);

        // Niveau 3 : Enfants de Surgelé
        $surgeleViande = (new FamilyLogDataBuilder())
            ->create('Viande')
            ->withUuid(FamilyLogDataBuilder::SURGELE_VIANDE_UUID)
            ->withParent($surgele)
            ->build()
        ;
        $this->familyLogRepository->save($surgeleViande);

        $surgeleFruitsLegumes = (new FamilyLogDataBuilder())
            ->create('Fruits & Légumes')
            ->withUuid(FamilyLogDataBuilder::SURGELE_FRUITS_LEGUMES_UUID)
            ->withParent($surgele)
            ->build()
        ;
        $this->familyLogRepository->save($surgeleFruitsLegumes);

        // Niveau 3 : Enfants de Frais
        $fraisViande = (new FamilyLogDataBuilder())
            ->create('Viande')
            ->withUuid(FamilyLogDataBuilder::FRAIS_VIANDE_UUID)
            ->withParent($frais)
            ->build()
        ;
        $this->familyLogRepository->save($fraisViande);

        $fraisFruitsLegumes = (new FamilyLogDataBuilder())
            ->create('Fruits & Légumes')
            ->withUuid(FamilyLogDataBuilder::FRAIS_FRUITS_LEGUMES_UUID)
            ->withParent($frais)
            ->build()
        ;
        $this->familyLogRepository->save($fraisFruitsLegumes);

        // Niveau 2 : Enfants de Non-alimentaire
        $emballage = (new FamilyLogDataBuilder())
            ->create('Emballage')
            ->withUuid(FamilyLogDataBuilder::EMBALLAGE_UUID)
            ->withParent($nonAlimentaire)
            ->build()
        ;
        $this->familyLogRepository->save($emballage);

        $hygiene = (new FamilyLogDataBuilder())
            ->create('Hygiène')
            ->withUuid(FamilyLogDataBuilder::HYGIENE_UUID)
            ->withParent($nonAlimentaire)
            ->build()
        ;
        $this->familyLogRepository->save($hygiene);

        return [
            'alimentaire' => $alimentaire,
            'surgele' => $surgele,
            'surgeleViande' => $surgeleViande,
            'surgeleFruitsLegumes' => $surgeleFruitsLegumes,
            'frais' => $frais,
            'fraisViande' => $fraisViande,
            'fraisFruitsLegumes' => $fraisFruitsLegumes,
            'epicerie' => $epicerie,
            'nonAlimentaire' => $nonAlimentaire,
            'emballage' => $emballage,
            'hygiene' => $hygiene,
        ];
    }

    /**
     * @param array{
     *     surgele: FamilyLog,
     *     frais: FamilyLog,
     *     epicerie: FamilyLog,
     *     fraisFruitsLegumes: FamilyLog
     * } $familyLogs
     *
     * @return array{
     *     reserveNegative: ZoneStorage,
     *     reservePositive: ZoneStorage,
     *     reserveSeche: ZoneStorage,
     *     reserveMaraichere: ZoneStorage
     * }
     */
    public function createStandardZoneStorages(array $familyLogs): array
    {
        $reserveNegative = (new ZoneStorageDataBuilder())
            ->create('Réserve négative', $familyLogs['surgele'])
            ->withUuid(ZoneStorageDataBuilder::RESERVE_NEGATIVE_UUID)
            ->build()
        ;
        $this->zoneStorageRepository->save($reserveNegative);

        $reservePositive = (new ZoneStorageDataBuilder())
            ->create('Réserve positive', $familyLogs['frais'])
            ->withUuid(ZoneStorageDataBuilder::RESERVE_POSITIVE_UUID)
            ->build()
        ;
        $this->zoneStorageRepository->save($reservePositive);

        $reserveSeche = (new ZoneStorageDataBuilder())
            ->create('Réserve sèche', $familyLogs['epicerie'])
            ->withUuid(ZoneStorageDataBuilder::RESERVE_SECHE_UUID)
            ->build()
        ;
        $this->zoneStorageRepository->save($reserveSeche);

        $reserveMaraichere = (new ZoneStorageDataBuilder())
            ->create('Réserve maraîchère', $familyLogs['fraisFruitsLegumes'])
            ->withUuid(ZoneStorageDataBuilder::RESERVE_MARAICHERE_UUID)
            ->build()
        ;
        $this->zoneStorageRepository->save($reserveMaraichere);

        return [
            'reserveNegative' => $reserveNegative,
            'reservePositive' => $reservePositive,
            'reserveSeche' => $reserveSeche,
            'reserveMaraichere' => $reserveMaraichere,
        ];
    }
}
