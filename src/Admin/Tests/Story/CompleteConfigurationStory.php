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

use Admin\Adapters\Gateway\ORM\Repository\DoctrineArticleRepository;
use Admin\Entities\Article\Article;
use Admin\Entities\Company;
use Admin\Entities\FamilyLog\FamilyLog;
use Admin\Entities\Supplier\Supplier;
use Admin\Entities\Tax\Tax;
use Admin\Entities\Unit\Unit;
use Admin\Entities\ZoneStorage\ZoneStorage;
use Admin\Tests\DataBuilder\ArticleDataBuilder;
use Faker\Factory;

final readonly class CompleteConfigurationStory
{
    public function __construct(
        public InitialConfigurationStory $initialStory,
        public FamilyLogConfigurationStory $familyLogStory,
        public SupplierConfigurationStory $supplierStory,
        private DoctrineArticleRepository $articleRepository,
    ) {
    }

    /**
     * Crée une configuration complète avec un Article.
     *
     * @return array{
     *     company: Company,
     *     unit: Unit,
     *     tax: Tax,
     *     familyLog: FamilyLog,
     *     zoneStorage: ZoneStorage,
     *     supplier: Supplier,
     *     article: Article
     * }
     */
    public function createCompleteConfigurationWithArticle(
        string $articleName = 'Tomates cerises',
        int $amount = 250,
    ): array {
        // 1. Configuration initiale (Company, Unit, Tax)
        $initial = $this->initialStory->createMinimalConfiguration();

        // 2. FamilyLog + ZoneStorage
        $familyConfig = $this->familyLogStory->createFamilyLogWithZone();

        // 3. Supplier
        $supplier = $this->supplierStory->createSupplier(
            'Maraîcher Bio',
            $familyConfig['familyLog']
        );

        // 4. Article
        $article = (new ArticleDataBuilder())
            ->create(
                name: $articleName,
                supplier: $supplier,
                tax: $initial['tax'],
                zoneStorages: [$familyConfig['zoneStorage']],
                familyLog: $familyConfig['familyLog'],
                packaging: [
                    [$initial['unit'], 1.0],
                    null,
                    null,
                ],
            )
            ->withAmount(amount: $amount)
            ->build()
        ;

        $this->articleRepository->save($article);

        return [
            'company' => $initial['company'],
            'unit' => $initial['unit'],
            'tax' => $initial['tax'],
            'familyLog' => $familyConfig['familyLog'],
            'zoneStorage' => $familyConfig['zoneStorage'],
            'supplier' => $supplier,
            'article' => $article,
        ];
    }

    /**
     * Crée un Article avec multiple packaging (trois niveaux).
     *
     * @param array<ZoneStorage> $zoneStorages
     */
    public function createArticleWithMultiPackaging(
        string $articleName,
        Supplier $supplier,
        Tax $tax,
        array $zoneStorages,
        FamilyLog $familyLog,
        Unit $unit1,
        Unit $unit2,
        Unit $unit3,
    ): Article {
        $article = (new ArticleDataBuilder())
            ->create(
                name: $articleName,
                supplier: $supplier,
                tax: $tax,
                zoneStorages: $zoneStorages,
                familyLog: $familyLog,
                packaging: [
                    [$unit1, 1.0],
                    [$unit2, 6.0],   // 1 pack = 6 unités
                    [$unit3, 24.0],  // 1 carton = 24 unités
                ],
            )
            ->build()
        ;

        $this->articleRepository->save($article);

        return $article;
    }

    /**
     * Crée un Article additionnel en réutilisant une configuration existante.
     * Utilise Faker pour générer un UUID unique.
     *
     * @param array{unit: Unit, tax: Tax, familyLog: FamilyLog, zoneStorage: ZoneStorage, supplier: Supplier} $config
     */
    public function createAdditionalArticle(
        array $config,
        string $articleName,
        int $amount = 250,
    ): Article {
        $faker = Factory::create('fr_FR');

        $article = (new ArticleDataBuilder())
            ->create(
                name: $articleName,
                supplier: $config['supplier'],
                tax: $config['tax'],
                zoneStorages: [$config['zoneStorage']],
                familyLog: $config['familyLog'],
                packaging: [
                    [$config['unit'], 1.0],
                    null,
                    null,
                ],
            )
            ->withUuid($faker->uuid())
            ->withAmount(amount: $amount)
            ->build()
        ;

        $this->articleRepository->save($article);

        return $article;
    }

    /**
     * Crée une configuration complète pour les formulaires Article
     * (hiérarchie FamilyLog 3 niveaux + 3 unités de packaging + ZoneStorage + Supplier).
     *
     * @return array{
     *     company: Company,
     *     tax: Tax,
     *     colis: Unit,
     *     piece: Unit,
     *     kilogramme: Unit,
     *     familyLog0: FamilyLog,
     *     familyLog1: FamilyLog,
     *     familyLog2: FamilyLog,
     *     zoneStorage: ZoneStorage,
     *     supplier: Supplier
     * }
     */
    public function createArticleFormConfiguration(
        string $companyName = 'Test company',
        string $taxName = 'TVA taux réduit',
        float $taxRate = 5.5,
    ): array {
        // 1. Configuration initiale (Company + Tax seulement, sans Unit pour éviter les doublons)
        $initial = $this->initialStory->createCompanyAndTax(
            companyName: $companyName,
            taxName: $taxName,
            taxRate: $taxRate
        );

        // 2. Unités de packaging (Colis, Pièce, Kilogramme) avec UUID constants
        $units = $this->initialStory->createPackagingUnits();

        // 3. Hiérarchie FamilyLog à 3 niveaux
        $familyHierarchy = $this->familyLogStory->createThreeLevelFamilyLogHierarchy();

        // 4. ZoneStorage liée au parent (Frais)
        $zoneStorage = $this->familyLogStory->createZoneStorageForFamilyLog(
            familyLog: $familyHierarchy['parent']
        );

        // 5. Supplier lié au grand-parent (Alimentaire)
        $supplier = $this->supplierStory->createSupplier(
            name: 'Supplier 1',
            familyLog: $familyHierarchy['grandParent']
        );

        return [
            'company' => $initial['company'],
            'tax' => $initial['tax'],
            'colis' => $units['colis'],
            'piece' => $units['piece'],
            'kilogramme' => $units['kilogramme'],
            'familyLog0' => $familyHierarchy['grandParent'],
            'familyLog1' => $familyHierarchy['parent'],
            'familyLog2' => $familyHierarchy['child'],
            'zoneStorage' => $zoneStorage,
            'supplier' => $supplier,
        ];
    }
}
