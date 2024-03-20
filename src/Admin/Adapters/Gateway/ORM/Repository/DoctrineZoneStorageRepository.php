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

use Admin\Adapters\Gateway\ORM\Entity\FamilyLog;
use Admin\Adapters\Gateway\ORM\Entity\ZoneStorage;
use Admin\Entities\Exception\FamilyLogNotFoundException;
use Admin\Entities\ZoneStorage\ZoneStorage as ZoneStorageDomain;
use Admin\Entities\ZoneStorage\ZoneStorageCollection;
use Admin\UseCases\Gateway\ZoneStorageRepository;
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

    public function __construct(ManagerRegistry $registry, private readonly DoctrineFamilyLogRepository $familyLogRepository)
    {
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

    public function save(ZoneStorageDomain $zoneStorage): void
    {
        $zoneStorageOrm = new ZoneStorage();
        $familyLog = $this->familyLogRepository->find($zoneStorage->familyLog()->uuid()->toString());
        if (!$familyLog instanceof FamilyLog) {
            throw new FamilyLogNotFoundException($zoneStorage->familyLog()->uuid()->toString());
        }

        $zoneStorageOrm->fromDomain($zoneStorage, $familyLog);

        $this->_em->persist($zoneStorageOrm);
        $this->_em->flush();
    }

    public function findAllZone(): ZoneStorageCollection
    {
        $zoneStorages = $this->findAll();
        $collection = new ZoneStorageCollection();

        foreach ($zoneStorages as $zoneStorage) {
            $collection->add($zoneStorage->toDomain());
        }

        return $collection;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException|UnexpectedResultException
     */
    public function hasZoneStorage(): bool
    {
        $alias = self::ALIAS;
        $count = $this->createQueryBuilder($alias)
            ->select("COUNT({$alias}.slug)")
            ->getQuery()
            ->getSingleScalarResult()
        ;

        if (!\is_int($count)) {
            throw new UnexpectedResultException();
        }

        return $count > 0;
    }
}
