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

namespace Admin\Tests\Adapters\Gateway\Cache;

use Admin\Adapters\Gateway\Cache\ConfigurationState;
use Admin\Adapters\Gateway\Cache\SymfonyConfigurationStateCache;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * @group unitTest
 *
 * @covers \Admin\Adapters\Gateway\Cache\SymfonyConfigurationStateCache
 */
final class SymfonyConfigurationStateCacheTest extends TestCase
{
    public function testGetReturnsNullWhenCacheMiss(): void
    {
        // Arrange
        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->method('isHit')->willReturn(false);

        $cachePool = $this->createMock(CacheItemPoolInterface::class);
        $cachePool->method('getItem')
            ->with(SymfonyConfigurationStateCache::CACHE_KEY)
            ->willReturn($cacheItem)
        ;

        $cache = new SymfonyConfigurationStateCache($cachePool);

        // Act
        $result = $cache->get();

        // Assert
        self::assertNull($result);
    }

    public function testGetReturnsStateWhenCacheHit(): void
    {
        // Arrange
        $expectedState = new ConfigurationState(
            hasCompany: true,
            hasUnit: true,
            hasTax: true,
            hasFamilyLog: true,
            hasZoneStorage: true,
            hasSupplier: true,
            hasArticle: true,
        );

        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->method('isHit')->willReturn(true);
        $cacheItem->method('get')->willReturn($expectedState);

        $cachePool = $this->createMock(CacheItemPoolInterface::class);
        $cachePool->method('getItem')
            ->with(SymfonyConfigurationStateCache::CACHE_KEY)
            ->willReturn($cacheItem)
        ;

        $cache = new SymfonyConfigurationStateCache($cachePool);

        // Act
        $result = $cache->get();

        // Assert
        self::assertSame($expectedState, $result);
    }

    public function testSaveStoresStateInCache(): void
    {
        // Arrange
        $state = new ConfigurationState(
            hasCompany: true,
            hasUnit: false,
            hasTax: false,
            hasFamilyLog: false,
            hasZoneStorage: false,
            hasSupplier: false,
            hasArticle: false,
        );

        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->expects(self::once())
            ->method('set')
            ->with($state)
            ->willReturnSelf()
        ;
        $cacheItem->expects(self::once())
            ->method('expiresAfter')
            ->with(SymfonyConfigurationStateCache::TTL)
            ->willReturnSelf()
        ;

        $cachePool = $this->createMock(CacheItemPoolInterface::class);
        $cachePool->method('getItem')
            ->with(SymfonyConfigurationStateCache::CACHE_KEY)
            ->willReturn($cacheItem)
        ;
        $cachePool->expects(self::once())
            ->method('save')
            ->with($cacheItem)
        ;

        $cache = new SymfonyConfigurationStateCache($cachePool);

        // Act
        $cache->save($state);
    }

    public function testInvalidateDeletesCache(): void
    {
        // Arrange
        $cachePool = $this->createMock(CacheItemPoolInterface::class);
        $cachePool->expects(self::once())
            ->method('deleteItem')
            ->with(SymfonyConfigurationStateCache::CACHE_KEY)
        ;

        $cache = new SymfonyConfigurationStateCache($cachePool);

        // Act
        $cache->invalidate();
    }
}
