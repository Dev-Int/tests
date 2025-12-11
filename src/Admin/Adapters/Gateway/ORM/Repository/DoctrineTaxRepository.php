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
use Admin\Entities\Exception\Tax\NoTaxRegistered;
use Admin\Entities\Exception\Tax\TaxNotFound;
use Admin\Entities\Repository\TaxRepository;
use Admin\Entities\Tax\Tax as TaxDomain;
use Admin\Entities\Tax\TaxCollection;
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
    public function exists(string $name, float $rate): bool
    {
        $alias = self::ALIAS;
        $tax = $this->createQueryBuilder($alias)
            ->where("{$alias}.rate = :rate")
            ->andWhere("{$alias}.name = :name")
            ->setParameters(['rate' => $rate, 'name' => $name])
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
            // @codeCoverageIgnoreStart
            throw new UnexpectedResultException('Integer expected!');
            // @codeCoverageIgnoreEnd
        }

        return $count > 0;
    }

    public function save(TaxDomain $tax): void
    {
        $taxOrm = (new Tax())->fromDomain($tax);

        $this->_em->persist($taxOrm);
        $this->_em->flush();
    }

    public function rename(TaxDomain $tax): void
    {
        $taxToRename = $this->find($tax->uuid()->toString());

        if (!$taxToRename instanceof Tax) {
            // @codeCoverageIgnoreStart
            throw new TaxNotFound($tax->uuid()->toString());
            // @codeCoverageIgnoreEnd
        }

        $taxToRename->setName($tax->name()->toString());

        $this->_em->flush();
    }

    public function revaluate(TaxDomain $tax): void
    {
        $taxToRevaluate = $this->find($tax->uuid()->toString());

        if (!$taxToRevaluate instanceof Tax) {
            // @codeCoverageIgnoreStart
            throw new TaxNotFound($tax->uuid()->toString());
            // @codeCoverageIgnoreEnd
        }

        $taxToRevaluate->setRate($tax->rate());

        $this->_em->flush();
    }

    public function getAllTaxes(): TaxCollection
    {
        $taxes = $this->findAll();
        $collection = new TaxCollection();

        if ($taxes === []) {
            throw new NoTaxRegistered();
        }

        foreach ($taxes as $tax) {
            $collection->add($tax->toDomain());
        }

        return $collection;
    }

    public function getById(string $uuid): TaxDomain
    {
        $tax = $this->find($uuid);

        if (!$tax instanceof Tax) {
            // @codeCoverageIgnoreStart
            throw new TaxNotFound($uuid);
            // @codeCoverageIgnoreEnd
        }

        return $tax->toDomain();
    }

    public function getByName(string $name): TaxDomain
    {
        $tax = $this->findOneBy(['name' => $name]);
        if (!$tax instanceof Tax) {
            throw new TaxNotFound($name);
        }

        return $tax->toDomain();
    }
}
