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

use Admin\Contracts\Services\Provider\ConfigurationServiceProvider;
use Admin\Entities\Repository\ArticleRepository;
use Admin\Entities\Repository\CompanyRepository;
use Admin\Entities\Repository\FamilyLogRepository;
use Admin\Entities\Repository\SupplierRepository;
use Admin\Entities\Repository\TaxRepository;
use Admin\Entities\Repository\UnitRepository;
use Admin\Entities\Repository\ZoneStorageRepository;

final readonly class ConfigurationService implements ConfigurationServiceProvider
{
    public function __construct(
        private CompanyRepository $companyRepository,
        private UnitRepository $unitRepository,
        private TaxRepository $taxRepository,
        private FamilyLogRepository $familyLogRepository,
        private ZoneStorageRepository $zoneStorageRepository,
        private SupplierRepository $supplierRepository,
        private ArticleRepository $articleRepository,
    ) {
    }

    public function isConfigured(): bool
    {
        return $this->isArticleConfigured();
    }

    public function isCompanyConfigured(): bool
    {
        return $this->companyRepository->hasCompany();
    }

    public function isUnitConfigured(): bool
    {
        $hasUnit = $this->unitRepository->hasUnit();

        return $this->isCompanyConfigured() && $hasUnit;
    }

    public function isTaxConfigured(): bool
    {
        $hasTax = $this->taxRepository->hasTax();

        return $this->isUnitConfigured() && $hasTax;
    }

    public function isApplicationConfigured(): bool
    {
        return $this->isTaxConfigured();
    }

    public function isFamilyLogConfigured(): bool
    {
        $hasFamilyLog = $this->familyLogRepository->hasFamilyLog();

        return $this->isApplicationConfigured() && $hasFamilyLog;
    }

    public function isZoneStorageConfigured(): bool
    {
        $hasZoneStorage = $this->zoneStorageRepository->hasZoneStorage();

        return $this->isFamilyLogConfigured() && $hasZoneStorage;
    }

    public function isSupplierConfigured(): bool
    {
        $hasSupplier = $this->supplierRepository->hasSupplier();

        return $this->isZoneStorageConfigured() && $hasSupplier;
    }

    public function isArticleConfigured(): bool
    {
        $hasArticle = $this->articleRepository->hasArticle();

        return $this->isSupplierConfigured() && $hasArticle;
    }
}
