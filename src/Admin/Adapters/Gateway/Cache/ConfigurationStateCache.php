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
 * Interface pour le cache de l'état de configuration.
 */
interface ConfigurationStateCache
{
    /**
     * Récupère l'état de configuration depuis le cache.
     */
    public function get(): ?ConfigurationState;

    /**
     * Sauvegarde l'état de configuration dans le cache.
     */
    public function save(ConfigurationState $state): void;

    /**
     * Invalide le cache (appelé lors de création/suppression d'entités).
     */
    public function invalidate(): void;
}
