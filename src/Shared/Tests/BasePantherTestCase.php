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

namespace App\Shared\Tests;

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
use Symfony\Component\Panther\PantherTestCase;

/**
 * Base class for E2E tests with Panther.
 * Uses LiipTestFixturesBundle for database reset before each test.
 */
class BasePantherTestCase extends PantherTestCase
{
    protected ?AbstractDatabaseTool $databaseTool = null;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        self::stopWebServer();
        parent::setUp();

        /** @var DatabaseToolCollection $databaseToolCollection */
        $databaseToolCollection = static::getContainer()->get(DatabaseToolCollection::class);
        $this->databaseTool = $databaseToolCollection->get();

        // Purge the database before each test for E2E tests
        $this->databaseTool->loadFixtures([]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->databaseTool = null;
    }

    /**
     * Force flush and clear entity manager so the Panther server can see the data.
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
     * Create minimal configuration entities required for ConfigurationService::isConfigured().
     * Creates one instance of each required entity: Company, Unit, Tax, FamilyLog, ZoneStorage, Supplier, Article.
     *
     * This is useful for E2E tests that need a configured system but don't care about specific data.
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

        // Create Company
        $company = (new CompanyDataBuilder())->create('Dev-Int Création')->build();
        $companyRepository->save($company);

        // Create Unit
        $colis = (new UnitDataBuilder())->create('Colis', 'cls')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $unitRepository->save($colis);

        // Create Tax
        $tax = (new TaxDataBuilder())->create('TVA taux réduit', 5.5)
            ->withUuid($faker->uuid())
            ->build()
        ;
        $taxRepository->save($tax);

        // Create a FamilyLog
        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLogRepository->save($familyLog);

        // Create ZoneStorage
        $zoneStorage = (new ZoneStorageDataBuilder())
            ->create('Reserve froide', $familyLog)
            ->withUuid($faker->uuid())
            ->build()
        ;
        $zoneStorageRepository->save($zoneStorage);

        // Create Supplier
        $supplier = (new SupplierDataBuilder())
            ->create('Supplier 1', $familyLog)
            ->withUuid($faker->uuid())
            ->build()
        ;
        $supplierRepository->save($supplier);

        // Create Article
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

        // Flush data so the Panther server can see it
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
