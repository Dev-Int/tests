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

use Admin\Adapters\Gateway\ORM\Entity\Tax;
use Admin\Entities\Tax\Tax as TaxDomain;
use Admin\Entities\Tax\TaxCollection;
use Admin\UseCases\Gateway\TaxRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\UnexpectedResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template-extends ServiceEntityRepository<Tax>
 */
final class DoctrineTaxRepository extends ServiceEntityRepository implements TaxRepository
{
    public const ALIAS = 'tax';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tax::class);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function exists(float $rate): bool
    {
        $alias = self::ALIAS;
        $tax = $this->createQueryBuilder($alias)
            ->where("{$alias}.rate = :rate")
            ->setParameter('rate', $rate)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $tax !== null;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException|UnexpectedResultException
     */
    public function hasTax(): bool
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

    public function save(TaxDomain $tax): void
    {
        $taxOrm = (new Tax())->fromDomain($tax);

        $this->_em->persist($taxOrm);
        $this->_em->flush();
    }

    public function findAllTaxes(): TaxCollection
    {
        $taxes = $this->findAll();
        $collection = new TaxCollection();

        foreach ($taxes as $tax) {
            $collection->add($tax->toDomain());
        }

        return $collection;
    }
}
