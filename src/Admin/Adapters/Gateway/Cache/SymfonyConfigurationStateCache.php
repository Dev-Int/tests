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

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

/**
 * Implémentation Symfony du cache pour l'état de configuration.
 */
#[AsAlias(ConfigurationStateCache::class)]
final readonly class SymfonyConfigurationStateCache implements ConfigurationStateCache
{
    public const string CACHE_KEY = 'admin.configuration_state';
    public const int TTL = 3600; // 1 heure

    public function __construct(
        private CacheItemPoolInterface $cachePool,
    ) {
    }

    public function get(): ?ConfigurationState
    {
        $item = $this->cachePool->getItem(self::CACHE_KEY);

        if (!$item->isHit()) {
            return null;
        }

        $state = $item->get();

        return $state instanceof ConfigurationState ? $state : null;
    }

    public function save(ConfigurationState $state): void
    {
        $item = $this->cachePool->getItem(self::CACHE_KEY);
        $item->set($state);
        $item->expiresAfter(self::TTL);

        $this->cachePool->save($item);
    }

    public function invalidate(): void
    {
        $this->cachePool->deleteItem(self::CACHE_KEY);
    }
}
