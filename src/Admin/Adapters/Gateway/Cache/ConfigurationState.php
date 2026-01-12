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

namespace Admin\Adapters\Gateway\Cache;

/**
 * État de configuration de l'application, utilisé pour le cache.
 */
final readonly class ConfigurationState
{
    public function __construct(
        public bool $hasCompany,
        public bool $hasUnit,
        public bool $hasTax,
        public bool $hasFamilyLog,
        public bool $hasZoneStorage,
        public bool $hasSupplier,
        public bool $hasArticle,
    ) {
    }

    /**
     * Application configurée = Company + Unit + Tax.
     */
    public function isApplicationConfigured(): bool
    {
        return $this->hasCompany && $this->hasUnit && $this->hasTax;
    }

    /**
     * Application prête = toutes les entités présentes.
     */
    public function isApplicationReady(): bool
    {
        return $this->isApplicationConfigured()
            && $this->hasFamilyLog
            && $this->hasZoneStorage
            && $this->hasSupplier
            && $this->hasArticle;
    }
}
