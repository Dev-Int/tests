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
use Admin\Entities\Exception\FamilyLog\FamilyLogNotFound;
use Admin\Entities\Exception\FamilyLog\NoFamilyLogRegistered;
use Admin\Entities\FamilyLog\FamilyLog as FamilyLogDomain;
use Admin\Entities\FamilyLog\FamilyLogCollection;
use Admin\Entities\Repository\FamilyLogRepository;
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
    public function exists(string $label, ?FamilyLogDomain $parent = null): bool
    {
        $alias = self::ALIAS;
        $queryBuilder = $this->createQueryBuilder($alias)
            ->where("{$alias}.label = :label")
            ->setParameter('label', $label)
        ;

        if (!$parent instanceof FamilyLogDomain) {
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

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException|UnexpectedResultException
     */
    public function hasFamilyLog(): bool
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

    /**
     * @throws NonUniqueResultException
     */
    public function save(FamilyLogDomain $familyLog): void
    {
        $familyLogOrm = (new FamilyLog())->fromDomain($familyLog);

        $parent = null;
        if ($familyLog->parent() instanceof FamilyLogDomain) {
            $parent = $this->find($familyLog->parent()->uuid()->toString());

            if (!$parent instanceof FamilyLog) {
                // @codeCoverageIgnoreStart
                throw new FamilyLogNotFound($familyLog->parent()->slug());
                // @codeCoverageIgnoreEnd
            }
        }
        $familyLogOrm->setParent($parent);

        $this->getEntityManager()->persist($familyLogOrm);
        $this->getEntityManager()->flush();
    }

    public function updateLabel(FamilyLogDomain $familyLog): void
    {
        $familyLogToUpdate = $this->find($familyLog->uuid()->toString());

        if (!$familyLogToUpdate instanceof FamilyLog) {
            // @codeCoverageIgnoreStart
            throw new FamilyLogNotFound($familyLog->slug());
            // @codeCoverageIgnoreEnd
        }

        $familyLogToUpdate
            ->setLabel($familyLog->label()->toString())
            ->setSlug($familyLog->slug())
        ;
        if ($familyLogToUpdate->children()->count() > 0) {
            foreach ($familyLogToUpdate->children() as $familyLogChild) {
                $familyLogChild->updateSlug($familyLog->slug());
            }
        }

        $this->getEntityManager()->flush();
    }

    public function assignParent(FamilyLogDomain $familyLog, string $uuid): void
    {
        $familyLogToUpdate = $this->find($uuid);

        if (!$familyLogToUpdate instanceof FamilyLog) {
            // @codeCoverageIgnoreStart
            throw new FamilyLogNotFound($familyLog->slug());
            // @codeCoverageIgnoreEnd
        }

        if ($familyLog->parent() instanceof FamilyLogDomain) {
            $parent = $this->find($familyLog->parent()->uuid()->toString());

            if (!$parent instanceof FamilyLog) {
                // @codeCoverageIgnoreStart
                throw new FamilyLogNotFound($familyLog->parent()->slug());
                // @codeCoverageIgnoreEnd
            }

            $familyLogToUpdate->setParent($parent)
                ->setSlug($familyLog->slug())
                ->setPath($familyLog->path())
            ;
        }

        if ($familyLogToUpdate->children()->count() > 0) {
            foreach ($familyLogToUpdate->children() as $child) {
                $child->updateSlug($familyLog->slug());
            }
        }

        $this->getEntityManager()->flush();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getByUuid(ResourceUuid $uuid): FamilyLogDomain
    {
        $alias = self::ALIAS;
        $familyLog = $this->createQueryBuilder($alias)
            ->where("{$alias}.uuid = :uuid")
            ->setParameter('uuid', $uuid->toString())
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (!$familyLog instanceof FamilyLog) {
            // @codeCoverageIgnoreStart
            throw new FamilyLogNotFound($uuid->toString());
            // @codeCoverageIgnoreEnd
        }

        return $familyLog->toDomain($familyLog->parent());
    }

    public function getByUuidWithChildren(ResourceUuid $uuid): FamilyLogDomain
    {
        $alias = self::ALIAS;
        $familyLogOrm = $this->createQueryBuilder($alias)
            ->where("{$alias}.uuid = :uuid")
            ->setParameter('uuid', $uuid->toString())
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (!$familyLogOrm instanceof FamilyLog) {
            // @codeCoverageIgnoreStart
            throw new FamilyLogNotFound($uuid->toString());
            // @codeCoverageIgnoreEnd
        }

        return $this->toDomainWithChildren($familyLogOrm, $familyLogOrm->parent());
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getBySlug(string $slug): FamilyLogDomain
    {
        $alias = self::ALIAS;
        $familyLog = $this->createQueryBuilder($alias)
            ->where("{$alias}.slug = :slug")
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (!$familyLog instanceof FamilyLog) {
            // @codeCoverageIgnoreStart
            throw new FamilyLogNotFound($slug);
            // @codeCoverageIgnoreEnd
        }

        return $familyLog->toDomain($familyLog->parent());
    }

    public function getFamilyLogsOrderingBySlug(): FamilyLogCollection
    {
        $collection = new FamilyLogCollection();
        $alias = self::ALIAS;
        $familyLogs = $this->createQueryBuilder($alias)
            ->orderBy("{$alias}.slug", 'ASC')
            ->getQuery()
            ->getResult()
        ;

        if (!\is_array($familyLogs)) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('array expected');
            // @codeCoverageIgnoreEnd
        }

        if ($familyLogs === []) {
            throw new NoFamilyLogRegistered();
        }

        foreach ($familyLogs as $familyLog) {
            if (!$familyLog instanceof FamilyLog) {
                // @codeCoverageIgnoreStart
                throw new \RuntimeException(\sprintf('%s expected', FamilyLog::class));
                // @codeCoverageIgnoreEnd
            }

            $collection->add($familyLog->toDomain());
        }

        return $collection;
    }

    private function toDomainWithChildren(FamilyLog $familyLogOrm, ?FamilyLog $parentOrm): FamilyLogDomain
    {
        $familyLogDomain = $familyLogOrm->toDomain($parentOrm);

        foreach ($familyLogOrm->children() as $childOrm) {
            $childDomain = $this->toDomainWithChildren($childOrm, $familyLogOrm);
            $familyLogDomain->addChild($childDomain);
        }

        return $familyLogDomain;
    }
}
