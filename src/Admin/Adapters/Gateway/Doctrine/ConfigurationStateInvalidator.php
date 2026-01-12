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

use Admin\Adapters\Gateway\Cache\ConfigurationState;
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
 *
 * Optimisations :
 * - postPersist : invalide seulement si le cache indique que ce type d'entité n'existe pas encore
 *   (évite les invalidations inutiles lors d'ajouts bulk)
 * - postRemove : invalide toujours (les suppressions bulk sont rares pour les entités de config)
 * - postUpdate : non écouté car le cache vérifie l'existence, pas le contenu
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

    public function postPersist(object $entity): void
    {
        if ($this->shouldInvalidateOnPersist($entity)) {
            $this->cache->invalidate();
        }
    }

    public function postRemove(): void
    {
        $this->cache->invalidate();
    }

    /**
     * Vérifie si l'invalidation est nécessaire : seulement si le cache indique
     * que ce type d'entité n'existe pas encore.
     */
    private function shouldInvalidateOnPersist(object $entity): bool
    {
        $state = $this->cache->get();

        if (!$state instanceof ConfigurationState) {
            return true;
        }

        return match (true) {
            $entity instanceof Company => !$state->hasCompany,
            $entity instanceof Unit => !$state->hasUnit,
            $entity instanceof Tax => !$state->hasTax,
            $entity instanceof FamilyLog => !$state->hasFamilyLog,
            $entity instanceof ZoneStorage => !$state->hasZoneStorage,
            $entity instanceof Supplier => !$state->hasSupplier,
            $entity instanceof Article => !$state->hasArticle,
            default => true,
        };
    }
}
