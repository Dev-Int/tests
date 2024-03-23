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
use Admin\Entities\Exception\FamilyLogNotFoundException;
use Admin\Entities\FamilyLog as FamilyLogDomain;
use Admin\Entities\FamilyLogCollection;
use Admin\UseCases\Gateway\FamilyLogRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\UnexpectedResultException;
use Doctrine\Persistence\ManagerRegistry;
use Shared\Entities\ResourceUuid;

/**
 * @template-extends ServiceEntityRepository<FamilyLog>
 */
final class DoctrineFamilyLogRepository extends ServiceEntityRepository implements FamilyLogRepository
{
    public const ALIAS = 'family_log';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FamilyLog::class);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function save(FamilyLogDomain $familyLog): void
    {
        $familyLogOrm = new FamilyLog();
        $familyLogOrm->fromDomain($familyLog);

        $parent = null;
        if ($familyLog->parent() !== null) {
            $parent = $this->find($familyLog->parent()->uuid()->toString());

            if (!$parent instanceof FamilyLog) {
                throw new FamilyLogNotFoundException($familyLog->parent()->slug());
            }
        }
        $familyLogOrm->setParent($parent);

        $this->_em->persist($familyLogOrm);
        $this->_em->flush();
    }

    public function findByUuid(ResourceUuid $uuid): FamilyLogDomain
    {
        $alias = self::ALIAS;
        $familyLog = $this->createQueryBuilder($alias)
            ->where("{$alias}.uuid = :uuid")
            ->setParameter('uuid', $uuid->toString())
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (!$familyLog instanceof FamilyLog) {
            throw new FamilyLogNotFoundException($uuid->toString());
        }

        return $familyLog->toDomain();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function exists(string $label, ?FamilyLogDomain $parent = null): bool
    {
        $alias = self::ALIAS;
        $queryBuilder = $this->createQueryBuilder($alias)
            ->where("{$alias}.label = :label")
            ->setParameter('label', $label)
        ;

        if ($parent === null) {
            $queryBuilder->andWhere("{$alias}.parent IS NULL");
        } else {
            $parentOrm = $this->find($parent->uuid()->toString());
            $queryBuilder
                ->andWhere("{$alias}.parent = :parent")
                ->setParameter('parent', $parentOrm)
            ;
        }
        $familyLog = $queryBuilder
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $familyLog !== null;
    }

    public function findFamilyLogsOrderingBySlug(): FamilyLogCollection
    {
        $collection = new FamilyLogCollection();
        $alias = self::ALIAS;
        $familyLogs = $this->createQueryBuilder($alias)
            ->orderBy("{$alias}.slug", 'ASC')
            ->getQuery()
            ->getResult()
        ;

        if (!\is_array($familyLogs)) {
            throw new \RuntimeException('array expected');
        }

        foreach ($familyLogs as $familyLog) {
            if (!$familyLog instanceof FamilyLog) {
                throw new \RuntimeException(sprintf('%s expected', FamilyLog::class));
            }

            $collection->add($familyLog->toDomain());
        }

        return $collection;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findBySlug(string $slug): FamilyLogDomain
    {
        $alias = self::ALIAS;
        $familyLog = $this->createQueryBuilder($alias)
            ->where("{$alias}.slug = :slug")
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (!$familyLog instanceof FamilyLog) {
            throw new FamilyLogNotFoundException($slug);
        }

        return $familyLog->toDomain();
    }

    public function updateLabel(FamilyLogDomain $familyLog): void
    {
        $familyLogToUpdate = $this->find($familyLog->uuid()->toString());
        if (!$familyLogToUpdate instanceof FamilyLog) {
            throw new FamilyLogNotFoundException($familyLog->slug());
        }

        $familyLogToUpdate->setLabel($familyLog->label()->toString());

        $this->_em->flush();
    }

    public function assignParent(FamilyLogDomain $familyLog, string $uuid): void
    {
        $familyLogToUpdate = $this->find($uuid);
        if (!$familyLogToUpdate instanceof FamilyLog) {
            throw new FamilyLogNotFoundException($familyLog->slug());
        }

        if ($familyLog->parent() !== null) {
            $parent = $this->find($familyLog->parent()->uuid()->toString());
            if (!$parent instanceof FamilyLog) {
                throw new FamilyLogNotFoundException($familyLog->parent()->slug());
            }

            $familyLogToUpdate->setParent($parent)
                ->setSlug($familyLog->slug())
                ->setPath($familyLog->path())
            ;
        }

        $this->_em->flush();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException|UnexpectedResultException
     */
    public function hasFamilyLog(): bool
    {
        $alias = self::ALIAS;
        $count = $this->createQueryBuilder($alias)
            ->select("COUNT({$alias}.uuid)")
            ->getQuery()
            ->getSingleScalarResult()
        ;

        if (!\is_int($count)) {
            throw new UnexpectedResultException();
        }

        return $count > 0;
    }
}
