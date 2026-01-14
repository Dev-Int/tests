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

namespace Admin\Tests\Adapters\Gateway\Doctrine;

use Admin\Adapters\Gateway\Cache\ConfigurationState;
use Admin\Adapters\Gateway\Cache\ConfigurationStateCache;
use Admin\Adapters\Gateway\Doctrine\ConfigurationStateInvalidator;
use Admin\Adapters\Gateway\ORM\Entity\Article\Article;
use Admin\Adapters\Gateway\ORM\Entity\Company;
use Admin\Adapters\Gateway\ORM\Entity\FamilyLog\FamilyLog;
use Admin\Adapters\Gateway\ORM\Entity\Supplier;
use Admin\Adapters\Gateway\ORM\Entity\Tax;
use Admin\Adapters\Gateway\ORM\Entity\Unit;
use Admin\Adapters\Gateway\ORM\Entity\ZoneStorage;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Admin\Adapters\Gateway\Doctrine\ConfigurationStateInvalidator
 */
final class ConfigurationStateInvalidatorTest extends TestCase
{
    /**
     * @return \Generator<string, array{class-string, bool, bool, bool, bool, bool, bool, bool}>
     */
    public static function providePostPersistSkipsInvalidationWhenEntityTypeAlreadyExistsCases(): iterable
    {
        // Chaque entité requiert les précédentes (dépendance en chaîne)
        // [entityClass, hasCompany, hasUnit, hasTax, hasFamilyLog, hasZoneStorage, hasSupplier, hasArticle]
        yield 'Company' => [Company::class, true, false, false, false, false, false, false];

        yield 'Unit' => [Unit::class, true, true, false, false, false, false, false];

        yield 'Tax' => [Tax::class, true, true, true, false, false, false, false];

        yield 'FamilyLog' => [FamilyLog::class, true, true, true, true, false, false, false];

        yield 'ZoneStorage' => [ZoneStorage::class, true, true, true, true, true, false, false];

        yield 'Supplier' => [Supplier::class, true, true, true, true, true, true, false];

        yield 'Article' => [Article::class, true, true, true, true, true, true, true];
    }

    public function testPostPersistInvalidatesCacheWhenEntityTypeDoesNotExist(): void
    {
        // Arrange
        $cache = $this->createMock(ConfigurationStateCache::class);
        $cache->method('get')->willReturn(null);
        $cache->expects(self::once())->method('invalidate');

        $invalidator = new ConfigurationStateInvalidator($cache);

        // Act
        $invalidator->postPersist($this->createMock(Company::class));
    }

    /**
     * @dataProvider providePostPersistSkipsInvalidationWhenEntityTypeAlreadyExistsCases
     *
     * @param class-string $entityClass
     */
    public function testPostPersistSkipsInvalidationWhenEntityTypeAlreadyExists(
        string $entityClass,
        bool $hasCompany,
        bool $hasUnit,
        bool $hasTax,
        bool $hasFamilyLog,
        bool $hasZoneStorage,
        bool $hasSupplier,
        bool $hasArticle,
    ): void {
        // Arrange
        $state = new ConfigurationState(
            hasCompany: $hasCompany,
            hasUnit: $hasUnit,
            hasTax: $hasTax,
            hasFamilyLog: $hasFamilyLog,
            hasZoneStorage: $hasZoneStorage,
            hasSupplier: $hasSupplier,
            hasArticle: $hasArticle,
        );

        $cache = $this->createMock(ConfigurationStateCache::class);
        $cache->method('get')->willReturn($state);
        $cache->expects(self::never())->method('invalidate');

        $invalidator = new ConfigurationStateInvalidator($cache);

        // Act
        $invalidator->postPersist($this->createMock($entityClass));
    }

    public function testPostRemoveInvalidatesCache(): void
    {
        // Arrange
        $cache = $this->createMock(ConfigurationStateCache::class);
        $cache->expects(self::once())->method('invalidate');

        $invalidator = new ConfigurationStateInvalidator($cache);

        // Act
        $invalidator->postRemove();
    }
}
