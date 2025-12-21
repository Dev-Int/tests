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

namespace Admin\Adapters\Gateway\ORM\Repository;

use Admin\Adapters\Gateway\ORM\Entity\FamilyLog\FamilyLog;
use Admin\Adapters\Gateway\ORM\Entity\ZoneStorage;
use Admin\Entities\Exception\FamilyLog\FamilyLogNotFound;
use Admin\Entities\Exception\ZoneStorage\NoZoneStorageRegistered;
use Admin\Entities\Exception\ZoneStorage\ZoneStorageNotFound;
use Admin\Entities\Repository\ZoneStorageRepository;
use Admin\Entities\ZoneStorage\ZoneStorage as ZoneStorageDomain;
use Admin\Entities\ZoneStorage\ZoneStorageCollection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\UnexpectedResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template-extends ServiceEntityRepository<ZoneStorage>
 */
final class DoctrineZoneStorageRepository extends ServiceEntityRepository implements ZoneStorageRepository
{
    public const ALIAS = 'zone_storage';

    public function __construct(
        ManagerRegistry $registry,
        private readonly DoctrineFamilyLogRepository $familyLogRepository
    ) {
        parent::__construct($registry, ZoneStorage::class);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function exists(string $label): bool
    {
        $alias = self::ALIAS;
        $zoneStorage = $this->createQueryBuilder($alias)
            ->where("{$alias}.label = :label")
            ->setParameter('label', $label)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $zoneStorage !== null;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException|UnexpectedResultException
     */
    public function hasZoneStorage(): bool
    {
        $alias = self::ALIAS;
        $count = $this->createQueryBuilder($alias)
            ->select('COUNT(1)')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        if (!\is_int($count)) {
            // @codeCoverageIgnoreStart
            throw new UnexpectedResultException('Integer expected!');
            // @codeCoverageIgnoreEnd
        }

        return $count > 0;
    }

    public function save(ZoneStorageDomain $zoneStorage): void
    {
        $zoneStorageOrm = new ZoneStorage();
        $familyLog = $this->familyLogRepository->find($zoneStorage->familyLog()->uuid()->toString());

        if (!$familyLog instanceof FamilyLog) {
            // @codeCoverageIgnoreStart
            throw new FamilyLogNotFound($zoneStorage->familyLog()->uuid()->toString());
            // @codeCoverageIgnoreEnd
        }

        $zoneStorageOrm->fromDomain($zoneStorage, $familyLog);

        $this->getEntityManager()->persist($zoneStorageOrm);
        $this->getEntityManager()->flush();
    }

    public function changeLabel(ZoneStorageDomain $zoneStorage): void
    {
        $zoneStorageToUpdate = $this->find($zoneStorage->uuid()->toString());

        if (!$zoneStorageToUpdate instanceof ZoneStorage) {
            // @codeCoverageIgnoreStart
            throw new ZoneStorageNotFound($zoneStorage->uuid()->toString());
            // @codeCoverageIgnoreEnd
        }

        $zoneStorageToUpdate->setLabel($zoneStorage->label()->toString())
            ->setSlug($zoneStorage->slug())
        ;

        $this->getEntityManager()->flush();
    }

    public function changeFamilyLog(ZoneStorageDomain $zoneStorage): void
    {
        $familyLog = $this->familyLogRepository->find($zoneStorage->familyLog()->uuid()->toString());

        if (!$familyLog instanceof FamilyLog) {
            // @codeCoverageIgnoreStart
            throw new FamilyLogNotFound($zoneStorage->familyLog()->uuid()->toString());
            // @codeCoverageIgnoreEnd
        }

        $zoneStorageToUpdate = $this->find($zoneStorage->uuid()->toString());
        if (!$zoneStorageToUpdate instanceof ZoneStorage) {
            // @codeCoverageIgnoreStart
            throw new ZoneStorageNotFound($zoneStorage->slug());
            // @codeCoverageIgnoreEnd
        }

        $zoneStorageToUpdate->setFamilyLog($familyLog);

        $this->getEntityManager()->flush();
    }

    public function getAllZones(): ZoneStorageCollection
    {
        $zoneStorages = $this->findAll();
        $collection = new ZoneStorageCollection();

        if ($zoneStorages === []) {
            throw new NoZoneStorageRegistered();
        }

        foreach ($zoneStorages as $zoneStorage) {
            $collection->add($zoneStorage->toDomain());
        }

        return $collection;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getBySlug(string $slug): ZoneStorageDomain
    {
        $alias = self::ALIAS;
        $zoneStorage = $this->createQueryBuilder($alias)
            ->where("{$alias}.slug = :slug")
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (!$zoneStorage instanceof ZoneStorage) {
            // @codeCoverageIgnoreStart
            throw new ZoneStorageNotFound($slug);
            // @codeCoverageIgnoreEnd
        }

        return $zoneStorage->toDomain();
    }
}
