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
use Doctrine\Persistence\ManagerRegistry;

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
        $parent = null;
        $level = 0;
        $familyLogOrm = new FamilyLog();
        $familyLogOrm->fromDomain($familyLog);

        if ($familyLog->parent() !== null) {
            $parent = $this->findBySlug($familyLog->parent()->slug());
            $level = $parent->level() + 1;
        }

        $familyLogOrm->setParent($parent);
        $familyLogOrm->setLevel($level);

        $this->_em->persist($familyLogOrm);
        $this->_em->flush();
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
            $queryBuilder
                ->andWhere("{$alias}.parent IS NULL")
            ;
        } else {
            $parentOrm = $this->find($parent->slug());
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

    public function findFamilyLogs(): FamilyLogCollection
    {
        $collection = new FamilyLogCollection();
        $familyLogs = $this->findAll();

        foreach ($familyLogs as $familyLog) {
            $collection->add($familyLog->toDomain());
        }

        return $collection;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findBySlug(string $slug): FamilyLog
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

        return $familyLog;
    }
}
