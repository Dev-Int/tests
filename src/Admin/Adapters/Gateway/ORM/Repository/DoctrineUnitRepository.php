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
use Admin\Entities\Unit as UnitDomain;
use Admin\UseCases\Gateway\UnitRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

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
    public function exists(string $label): bool
    {
        $alias = self::ALIAS;
        $unit = $this->createQueryBuilder($alias)
            ->where("{$alias}.label = :label")
            ->setParameter('label', $label)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $unit !== null;
    }

    public function save(UnitDomain $unit): void
    {
        $unitOrm = (new Unit())->fromDomain($unit);

        $this->_em->persist($unitOrm);
        $this->_em->flush();
    }
}
