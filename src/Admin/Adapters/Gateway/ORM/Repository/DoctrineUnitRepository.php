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

use Admin\Adapters\Gateway\ORM\Entity\Unit;
use Admin\Entities\Exception\UnitNotFoundException;
use Admin\Entities\Unit\Unit as UnitDomain;
use Admin\Entities\Unit\UnitCollection;
use Admin\UseCases\Gateway\UnitRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\UnexpectedResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template-extends ServiceEntityRepository<Unit>
 */
final class DoctrineUnitRepository extends ServiceEntityRepository implements UnitRepository
{
    public const ALIAS = 'unit';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Unit::class);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function exists(string $label, string $uuid): bool
    {
        $alias = self::ALIAS;
        $unit = $this->createQueryBuilder($alias)
            ->where("{$alias}.label = :label")
            ->andWhere("{$alias}.uuid != :uuid")
            ->setParameters(['label' => $label, 'uuid' => $uuid])
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $unit !== null;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException|UnexpectedResultException
     */
    public function hasUnit(): bool
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

    public function save(UnitDomain $unit): void
    {
        $unitOrm = (new Unit())->fromDomain($unit);

        $this->_em->persist($unitOrm);
        $this->_em->flush();
    }

    public function changeLabel(UnitDomain $unit): void
    {
        $unitToUpdate = $this->find($unit->uuid()->toString());
        if (!$unitToUpdate instanceof Unit) {
            throw new UnitNotFoundException($unit->slug());
        }

        $unitToUpdate->setLabel($unit->label()->toString())
            ->setAbbreviation($unit->abbreviation())
            ->setSlug($unit->slug())
        ;

        $this->_em->flush();
    }

    public function findAllUnits(): UnitCollection
    {
        $units = $this->findAll();
        $collection = new UnitCollection();

        foreach ($units as $unit) {
            $collection->add($unit->toDomain());
        }

        return $collection;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findBySlug(string $slug): UnitDomain
    {
        $alias = self::ALIAS;
        $unit = $this->createQueryBuilder($alias)
            ->where("{$alias}.slug = :slug")
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (!$unit instanceof Unit) {
            throw new UnitNotFoundException($slug);
        }

        return $unit->toDomain();
    }
}
