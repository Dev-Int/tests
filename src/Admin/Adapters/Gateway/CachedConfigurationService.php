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

namespace Admin\Adapters\Gateway;

use Admin\Adapters\Gateway\Cache\ConfigurationState;
use Admin\Adapters\Gateway\Cache\ConfigurationStateCache;
use Admin\Contracts\Services\Provider\ConfigurationServiceProvider;
use Admin\Entities\Repository\ArticleRepository;
use Admin\Entities\Repository\CompanyRepository;
use Admin\Entities\Repository\FamilyLogRepository;
use Admin\Entities\Repository\SupplierRepository;
use Admin\Entities\Repository\TaxRepository;
use Admin\Entities\Repository\UnitRepository;
use Admin\Entities\Repository\ZoneStorageRepository;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

/**
 * Service de configuration avec cache.
 *
 * Remplace ConfigurationService pour éviter les requêtes SQL à chaque requête HTTP.
 * Utilise un cache à deux niveaux :
 * - Request-scoped (mémoire) pour éviter les multiples lectures dans une même requête
 * - Pool Symfony (filesystem/Redis) pour persister entre les requêtes
 */
#[AsAlias(ConfigurationServiceProvider::class)]
final class CachedConfigurationService implements ConfigurationServiceProvider
{
    private ?ConfigurationState $requestScopedState = null;

    public function __construct(
        private readonly ConfigurationStateCache $cache,
        private readonly CompanyRepository $companyRepository,
        private readonly UnitRepository $unitRepository,
        private readonly TaxRepository $taxRepository,
        private readonly FamilyLogRepository $familyLogRepository,
        private readonly ZoneStorageRepository $zoneStorageRepository,
        private readonly SupplierRepository $supplierRepository,
        private readonly ArticleRepository $articleRepository,
    ) {
    }

    public function isCompanyConfigured(): bool
    {
        return $this->getOrLoadState()->hasCompany;
    }

    public function isUnitConfigured(): bool
    {
        $state = $this->getOrLoadState();

        return $state->hasCompany && $state->hasUnit;
    }

    public function isTaxConfigured(): bool
    {
        $state = $this->getOrLoadState();

        return $state->hasCompany && $state->hasUnit && $state->hasTax;
    }

    public function isApplicationConfigured(): bool
    {
        return $this->getOrLoadState()->isApplicationConfigured();
    }

    public function isFamilyLogConfigured(): bool
    {
        $state = $this->getOrLoadState();

        return $state->isApplicationConfigured() && $state->hasFamilyLog;
    }

    public function isZoneStorageConfigured(): bool
    {
        $state = $this->getOrLoadState();

        return $state->isApplicationConfigured() && $state->hasFamilyLog && $state->hasZoneStorage;
    }

    public function isSupplierConfigured(): bool
    {
        $state = $this->getOrLoadState();

        return $state->isApplicationConfigured()
            && $state->hasFamilyLog
            && $state->hasZoneStorage
            && $state->hasSupplier;
    }

    public function isArticleConfigured(): bool
    {
        return $this->getOrLoadState()->isApplicationReady();
    }

    public function isApplicationReady(): bool
    {
        return $this->getOrLoadState()->isApplicationReady();
    }

    private function getOrLoadState(): ConfigurationState
    {
        // 1. Cache request-scoped (mémoire)
        if ($this->requestScopedState instanceof ConfigurationState) {
            return $this->requestScopedState;
        }

        // 2. Cache Pool Symfony
        $cached = $this->cache->get();
        if ($cached instanceof ConfigurationState) {
            $this->requestScopedState = $cached;

            return $cached;
        }

        // 3. Fallback : charger depuis les repositories et mettre en cache
        $state = $this->loadStateFromRepositories();
        $this->cache->save($state);
        $this->requestScopedState = $state;

        return $state;
    }

    private function loadStateFromRepositories(): ConfigurationState
    {
        return new ConfigurationState(
            hasCompany: $this->companyRepository->hasCompany(),
            hasUnit: $this->unitRepository->hasUnit(),
            hasTax: $this->taxRepository->hasTax(),
            hasFamilyLog: $this->familyLogRepository->hasFamilyLog(),
            hasZoneStorage: $this->zoneStorageRepository->hasZoneStorage(),
            hasSupplier: $this->supplierRepository->hasSupplier(),
            hasArticle: $this->articleRepository->hasArticle(),
        );
    }
}
