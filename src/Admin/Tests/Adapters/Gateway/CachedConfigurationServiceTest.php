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

namespace Admin\Tests\Adapters\Gateway;

use Admin\Adapters\Gateway\Cache\ConfigurationState;
use Admin\Adapters\Gateway\Cache\ConfigurationStateCache;
use Admin\Adapters\Gateway\CachedConfigurationService;
use Admin\Entities\Repository\ArticleRepository;
use Admin\Entities\Repository\CompanyRepository;
use Admin\Entities\Repository\FamilyLogRepository;
use Admin\Entities\Repository\SupplierRepository;
use Admin\Entities\Repository\TaxRepository;
use Admin\Entities\Repository\UnitRepository;
use Admin\Entities\Repository\ZoneStorageRepository;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 *
 * @covers \Admin\Adapters\Gateway\CachedConfigurationService
 */
final class CachedConfigurationServiceTest extends TestCase
{
    public function testIsApplicationReadyReturnsCachedValue(): void
    {
        // Arrange
        $cachedState = new ConfigurationState(
            hasCompany: true,
            hasUnit: true,
            hasTax: true,
            hasFamilyLog: true,
            hasZoneStorage: true,
            hasSupplier: true,
            hasArticle: true,
        );

        $cache = $this->createMock(ConfigurationStateCache::class);
        $cache->expects(self::once())->method('get')->willReturn($cachedState);

        $service = $this->createService($cache);

        // Act
        $result = $service->isApplicationReady();

        // Assert
        self::assertTrue($result);
    }

    public function testIsApplicationReadyLoadsFromRepositoriesWhenCacheMiss(): void
    {
        // Arrange
        $cache = $this->createMock(ConfigurationStateCache::class);
        $cache->expects(self::once())->method('get')->willReturn(null);
        $cache->expects(self::once())->method('save');

        $service = $this->createService(
            $cache,
            hasCompany: true,
            hasUnit: true,
            hasTax: true,
            hasFamilyLog: true,
            hasZoneStorage: true,
            hasSupplier: true,
            hasArticle: true,
        );

        // Act
        $result = $service->isApplicationReady();

        // Assert
        self::assertTrue($result);
    }

    public function testIsApplicationConfiguredReturnsCachedValue(): void
    {
        // Arrange
        $cachedState = new ConfigurationState(
            hasCompany: true,
            hasUnit: true,
            hasTax: true,
            hasFamilyLog: false,
            hasZoneStorage: false,
            hasSupplier: false,
            hasArticle: false,
        );

        $cache = $this->createMock(ConfigurationStateCache::class);
        $cache->expects(self::once())->method('get')->willReturn($cachedState);

        $service = $this->createService($cache);

        // Act
        $result = $service->isApplicationConfigured();

        // Assert
        self::assertTrue($result);
    }

    public function testRequestScopedCacheAvoidsMultipleCacheReads(): void
    {
        // Arrange
        $cachedState = new ConfigurationState(
            hasCompany: true,
            hasUnit: true,
            hasTax: true,
            hasFamilyLog: true,
            hasZoneStorage: true,
            hasSupplier: true,
            hasArticle: true,
        );

        $cache = $this->createMock(ConfigurationStateCache::class);
        // Le cache ne doit être lu qu'une seule fois
        $cache->expects(self::once())->method('get')->willReturn($cachedState);

        $service = $this->createService($cache);

        // Act - Appels multiples dans la même requête
        $service->isApplicationReady();
        $service->isApplicationConfigured();
        $service->isCompanyConfigured();

        // Assert - Vérifié par l'expectation once() sur get()
    }

    public function testIsCompanyConfiguredReturnsCachedValue(): void
    {
        // Arrange
        $cachedState = new ConfigurationState(
            hasCompany: true,
            hasUnit: false,
            hasTax: false,
            hasFamilyLog: false,
            hasZoneStorage: false,
            hasSupplier: false,
            hasArticle: false,
        );

        $cache = $this->createMock(ConfigurationStateCache::class);
        $cache->expects(self::once())->method('get')->willReturn($cachedState);

        $service = $this->createService($cache);

        // Act
        $result = $service->isCompanyConfigured();

        // Assert
        self::assertTrue($result);
    }

    public function testIsUnitConfiguredReturnsCachedValue(): void
    {
        // Arrange
        $cachedState = new ConfigurationState(
            hasCompany: true,
            hasUnit: true,
            hasTax: false,
            hasFamilyLog: false,
            hasZoneStorage: false,
            hasSupplier: false,
            hasArticle: false,
        );

        $cache = $this->createMock(ConfigurationStateCache::class);
        $cache->expects(self::once())->method('get')->willReturn($cachedState);

        $service = $this->createService($cache);

        // Act
        $result = $service->isUnitConfigured();

        // Assert
        self::assertTrue($result);
    }

    public function testIsTaxConfiguredReturnsCachedValue(): void
    {
        // Arrange
        $cachedState = new ConfigurationState(
            hasCompany: true,
            hasUnit: true,
            hasTax: true,
            hasFamilyLog: false,
            hasZoneStorage: false,
            hasSupplier: false,
            hasArticle: false,
        );

        $cache = $this->createMock(ConfigurationStateCache::class);
        $cache->expects(self::once())->method('get')->willReturn($cachedState);

        $service = $this->createService($cache);

        // Act
        $result = $service->isTaxConfigured();

        // Assert
        self::assertTrue($result);
    }

    private function createService(
        ConfigurationStateCache $cache,
        bool $hasCompany = false,
        bool $hasUnit = false,
        bool $hasTax = false,
        bool $hasFamilyLog = false,
        bool $hasZoneStorage = false,
        bool $hasSupplier = false,
        bool $hasArticle = false,
    ): CachedConfigurationService {
        $companyRepo = $this->createMock(CompanyRepository::class);
        $companyRepo->method('hasCompany')->willReturn($hasCompany);

        $unitRepo = $this->createMock(UnitRepository::class);
        $unitRepo->method('hasUnit')->willReturn($hasUnit);

        $taxRepo = $this->createMock(TaxRepository::class);
        $taxRepo->method('hasTax')->willReturn($hasTax);

        $familyLogRepo = $this->createMock(FamilyLogRepository::class);
        $familyLogRepo->method('hasFamilyLog')->willReturn($hasFamilyLog);

        $zoneStorageRepo = $this->createMock(ZoneStorageRepository::class);
        $zoneStorageRepo->method('hasZoneStorage')->willReturn($hasZoneStorage);

        $supplierRepo = $this->createMock(SupplierRepository::class);
        $supplierRepo->method('hasSupplier')->willReturn($hasSupplier);

        $articleRepo = $this->createMock(ArticleRepository::class);
        $articleRepo->method('hasArticle')->willReturn($hasArticle);

        return new CachedConfigurationService(
            $cache,
            $companyRepo,
            $unitRepo,
            $taxRepo,
            $familyLogRepo,
            $zoneStorageRepo,
            $supplierRepo,
            $articleRepo,
        );
    }
}
