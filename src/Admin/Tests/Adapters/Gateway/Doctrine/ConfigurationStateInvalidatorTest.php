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

use Admin\Adapters\Gateway\Cache\ConfigurationStateCache;
use Admin\Adapters\Gateway\Doctrine\ConfigurationStateInvalidator;
use Admin\Adapters\Gateway\ORM\Entity\Company;
use Admin\Adapters\Gateway\ORM\Entity\Unit;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Admin\Adapters\Gateway\Doctrine\ConfigurationStateInvalidator
 */
final class ConfigurationStateInvalidatorTest extends TestCase
{
    public function testPostPersistInvalidatesCache(): void
    {
        // Arrange
        $cache = $this->createMock(ConfigurationStateCache::class);
        $cache->expects(self::once())->method('invalidate');

        $invalidator = new ConfigurationStateInvalidator($cache);
        $this->createMock(Company::class);

        // Act
        $invalidator->postPersist();
    }

    public function testPostRemoveInvalidatesCache(): void
    {
        // Arrange
        $cache = $this->createMock(ConfigurationStateCache::class);
        $cache->expects(self::once())->method('invalidate');

        $invalidator = new ConfigurationStateInvalidator($cache);
        $this->createMock(Unit::class);

        // Act
        $invalidator->postRemove();
    }
}
