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

namespace Admin\Adapters\Gateway\Doctrine;

use Admin\Adapters\Gateway\Cache\ConfigurationStateCache;
use Admin\Adapters\Gateway\ORM\Entity\Article\Article;
use Admin\Adapters\Gateway\ORM\Entity\Company;
use Admin\Adapters\Gateway\ORM\Entity\FamilyLog\FamilyLog;
use Admin\Adapters\Gateway\ORM\Entity\Supplier;
use Admin\Adapters\Gateway\ORM\Entity\Tax;
use Admin\Adapters\Gateway\ORM\Entity\Unit;
use Admin\Adapters\Gateway\ORM\Entity\ZoneStorage;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

/**
 * Invalide le cache de configuration quand une entité de configuration est créée/supprimée.
 */
#[AsEntityListener(event: Events::postPersist, entity: Company::class)]
#[AsEntityListener(event: Events::postRemove, entity: Company::class)]
#[AsEntityListener(event: Events::postPersist, entity: Unit::class)]
#[AsEntityListener(event: Events::postRemove, entity: Unit::class)]
#[AsEntityListener(event: Events::postPersist, entity: Tax::class)]
#[AsEntityListener(event: Events::postRemove, entity: Tax::class)]
#[AsEntityListener(event: Events::postPersist, entity: FamilyLog::class)]
#[AsEntityListener(event: Events::postRemove, entity: FamilyLog::class)]
#[AsEntityListener(event: Events::postPersist, entity: ZoneStorage::class)]
#[AsEntityListener(event: Events::postRemove, entity: ZoneStorage::class)]
#[AsEntityListener(event: Events::postPersist, entity: Supplier::class)]
#[AsEntityListener(event: Events::postRemove, entity: Supplier::class)]
#[AsEntityListener(event: Events::postPersist, entity: Article::class)]
#[AsEntityListener(event: Events::postRemove, entity: Article::class)]
final readonly class ConfigurationStateInvalidator
{
    public function __construct(
        private ConfigurationStateCache $cache,
    ) {
    }

    public function postPersist(): void
    {
        $this->cache->invalidate();
    }

    public function postRemove(): void
    {
        $this->cache->invalidate();
    }
}
