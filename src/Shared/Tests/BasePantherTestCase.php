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

namespace Shared\Tests;

use Admin\Adapters\Gateway\ORM\Repository\DoctrineArticleRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineCompanyRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineSupplierRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineTaxRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineUnitRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineZoneStorageRepository;
use Admin\Entities\Article\Article;
use Admin\Entities\Company;
use Admin\Entities\FamilyLog\FamilyLog;
use Admin\Entities\Supplier\Supplier;
use Admin\Entities\Tax\Tax;
use Admin\Entities\Unit\Unit;
use Admin\Entities\ZoneStorage\ZoneStorage;
use Admin\Tests\DataBuilder\ArticleDataBuilder;
use Admin\Tests\DataBuilder\CompanyDataBuilder;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use Admin\Tests\DataBuilder\ZoneStorageDataBuilder;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\NonUniqueResultException;
use Faker\Factory;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\Clock\SystemClock;
use Symfony\Component\Panther\PantherTestCase;

/**
 * Classe de base pour les tests E2E avec Panther.
 * Utilise LiipTestFixturesBundle pour réinitialiser la base de données avant chaque test.
 */
class BasePantherTestCase extends PantherTestCase
{
    protected ?AbstractDatabaseTool $databaseTool = null;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        self::stopWebServer();
        parent::setUp();

        // Réinitialise l'horloge à l'heure système avant chaque test
        // (comme dans BaseFunctionalTestCase)
        ClockFactory::initialize(new SystemClock());

        /** @var DatabaseToolCollection $databaseToolCollection */
        $databaseToolCollection = static::getContainer()->get(DatabaseToolCollection::class);
        $this->databaseTool = $databaseToolCollection->get();

        // Purge la base de données avant chaque test E2E
        $this->databaseTool->loadFixtures();
    }

    protected function tearDown(): void
    {
        // Reset ClockFactory pour éviter pollution vers autres tests
        ClockFactory::initialize(new SystemClock());

        parent::tearDown();
        $this->databaseTool = null;

        // Assure un état propre entre les tests
        self::ensureKernelShutdown();
        self::stopWebServer();
    }

    /**
     * Force le flush et le clear de l'EntityManager pour que le serveur Panther puisse voir les données.
     */
    protected function flushAndClearEntityManager(): void
    {
        /** @var Registry $doctrineService */
        $doctrineService = static::getContainer()->get('doctrine');
        $entityManager = $doctrineService->getManager();
        $entityManager->flush();
        $entityManager->clear();
    }

    /**
     * Crée les entités de configuration minimales requises pour ConfigurationService::isConfigured().
     * Crée une instance de chaque entité requise : Company, Unit, Tax, FamilyLog, ZoneStorage, Supplier, Article.
     *
     * Utile pour les tests E2E qui nécessitent un système configuré mais sans données spécifiques.
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
     *
     * @throws NonUniqueResultException
     */
    protected function createMinimalConfiguration(): array
    {
        $faker = Factory::create('fr_FR');

        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = static::getContainer()->get(DoctrineCompanyRepository::class);

        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = static::getContainer()->get(DoctrineUnitRepository::class);

        /** @var DoctrineTaxRepository $taxRepository */
        $taxRepository = static::getContainer()->get(DoctrineTaxRepository::class);

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = static::getContainer()->get(DoctrineFamilyLogRepository::class);

        /** @var DoctrineZoneStorageRepository $zoneStorageRepository */
        $zoneStorageRepository = static::getContainer()->get(DoctrineZoneStorageRepository::class);

        /** @var DoctrineSupplierRepository $supplierRepository */
        $supplierRepository = static::getContainer()->get(DoctrineSupplierRepository::class);

        /** @var DoctrineArticleRepository $articleRepository */
        $articleRepository = static::getContainer()->get(DoctrineArticleRepository::class);

        // Crée Company
        $company = (new CompanyDataBuilder())->create('Dev-Int Création')->build();
        $companyRepository->save($company);

        // Crée Unit
        $colis = (new UnitDataBuilder())->create('Colis', 'cls')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $unitRepository->save($colis);

        // Crée Tax
        $tax = (new TaxDataBuilder())->create('TVA taux réduit', 5.5)
            ->withUuid($faker->uuid())
            ->build()
        ;
        $taxRepository->save($tax);

        // Crée FamilyLog
        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLogRepository->save($familyLog);

        // Crée ZoneStorage
        $zoneStorage = (new ZoneStorageDataBuilder())
            ->create('Reserve froide', $familyLog)
            ->withUuid($faker->uuid())
            ->build()
        ;
        $zoneStorageRepository->save($zoneStorage);

        // Crée Supplier
        $supplier = (new SupplierDataBuilder())
            ->create('Supplier 1', $familyLog)
            ->withUuid($faker->uuid())
            ->build()
        ;
        $supplierRepository->save($supplier);

        // Crée Article
        $article = (new ArticleDataBuilder())->create(
            'Jambon Trad 6kg',
            $supplier,
            $tax,
            [$zoneStorage],
            $familyLog,
            [[$colis, 1.0], null, null]
        )
            ->withUuid($faker->uuid())
            ->build()
        ;
        $articleRepository->save($article);

        // Flush les données pour que le serveur Panther puisse les voir
        $this->flushAndClearEntityManager();

        return [
            'company' => $company,
            'unit' => $colis,
            'tax' => $tax,
            'familyLog' => $familyLog,
            'zoneStorage' => $zoneStorage,
            'supplier' => $supplier,
            'article' => $article,
        ];
    }
}
