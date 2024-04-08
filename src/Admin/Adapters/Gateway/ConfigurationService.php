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

use Admin\UseCases\Gateway\CompanyRepository;
use Admin\UseCases\Gateway\FamilyLogRepository;
use Admin\UseCases\Gateway\TaxRepository;
use Admin\UseCases\Gateway\UnitRepository;
use Admin\UseCases\Gateway\ZoneStorageRepository;

final readonly class ConfigurationService
{
    public function __construct(
        private CompanyRepository $companyRepository,
        private UnitRepository $unitRepository,
        private TaxRepository $taxRepository,
        private FamilyLogRepository $familyLogRepository,
        private ZoneStorageRepository $zoneStorageRepository
    ) {
    }

    public function isConfigured(): bool
    {
        $hasCompany = $this->companyRepository->hasCompany();
        $hasApplication = $this->unitRepository->hasUnit() && $this->taxRepository->hasTax();
        $hasFamilyLog = $this->familyLogRepository->hasFamilyLog();
        $hasZoneStorage = $this->zoneStorageRepository->hasZoneStorage();

        return $hasCompany && $hasApplication && $hasFamilyLog && $hasZoneStorage;
    }

    public function isApplicationConfigured(): bool
    {
        return $this->unitRepository->hasUnit() && $this->taxRepository->hasTax();
    }
}
