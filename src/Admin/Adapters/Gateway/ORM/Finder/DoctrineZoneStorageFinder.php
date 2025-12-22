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

namespace Admin\Adapters\Gateway\ORM\Finder;

use Admin\Adapters\Gateway\ORM\Entity\ZoneStorage as ZoneStorageOrm;
use Admin\Entities\ZoneStorage\ZoneStorage;
use Admin\UseCases\Gateway\Finder\ZoneStorageFinder;
use Doctrine\ORM\EntityManagerInterface;
use Shared\Entities\ResourceUuid;

final readonly class DoctrineZoneStorageFinder implements ZoneStorageFinder
{
    private const ALIAS = 'zone_storage';

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function findByUuid(ResourceUuid|string $uuid): ?ZoneStorage
    {
        $uuidStr = $uuid instanceof ResourceUuid ? $uuid->toString() : $uuid;
        $alias = self::ALIAS;

        $zoneStorageOrm = $this->entityManager->createQueryBuilder()
            ->select($alias)
            ->from(ZoneStorageOrm::class, $alias)
            ->where("{$alias}.uuid = :uuid")
            ->setParameter('uuid', $uuidStr)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (!$zoneStorageOrm instanceof ZoneStorageOrm) {
            return null;
        }

        return $zoneStorageOrm->toDomain();
    }

    /**
     * @return iterable<ZoneStorage>
     */
    public function findAllZoneStorages(): iterable
    {
        $alias = self::ALIAS;

        /** @var array<ZoneStorageOrm> $zoneStoragesOrm */
        $zoneStoragesOrm = $this->entityManager->createQueryBuilder()
            ->select($alias)
            ->from(ZoneStorageOrm::class, $alias)
            ->getQuery()
            ->getResult()
        ;

        foreach ($zoneStoragesOrm as $zoneStorageOrm) {
            yield $zoneStorageOrm->toDomain();
        }
    }
}
