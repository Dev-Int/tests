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
use Admin\Adapters\Gateway\ORM\Entity\Supplier;
use Admin\Entities\Exception\FamilyLogNotFoundException;
use Admin\Entities\Exception\NoSupplierRegisteredException;
use Admin\Entities\Exception\SupplierNotFoundException;
use Admin\Entities\Supplier\Supplier as SupplierDomain;
use Admin\Entities\Supplier\SupplierCollection;
use Admin\UseCases\Gateway\SupplierRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\UnexpectedResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template-extends ServiceEntityRepository<Supplier>
 */
final class DoctrineSupplierRepository extends ServiceEntityRepository implements SupplierRepository
{
    public const ALIAS = 'supplier';

    public function __construct(
        ManagerRegistry $registry,
        private readonly DoctrineFamilyLogRepository $familyLogRepository
    ) {
        parent::__construct($registry, Supplier::class);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function exists(string $name): bool
    {
        $alias = self::ALIAS;
        $supplier = $this->createQueryBuilder($alias)
            ->where("{$alias}.name = :name")
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $supplier !== null;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException|UnexpectedResultException
     */
    public function hasSupplier(): bool
    {
        $alias = self::ALIAS;
        $count = $this->createQueryBuilder($alias)
            ->select("COUNT({$alias}.slug)")
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

    public function save(SupplierDomain $supplier): void
    {
        $familyLog = $this->familyLogRepository->find($supplier->familyLog()->uuid()->toString());

        if (!$familyLog instanceof FamilyLog) {
            // @codeCoverageIgnoreStart
            throw new FamilyLogNotFoundException($supplier->familyLog()->uuid()->toString());
            // @codeCoverageIgnoreEnd
        }

        $this->_em->persist((new Supplier())->fromDomain($supplier, $familyLog));
        $this->_em->flush();
    }

    public function renameSupplier(SupplierDomain $supplier): void
    {
        $supplierToUpdate = $this->find($supplier->uuid()->toString());

        if (!$supplierToUpdate instanceof Supplier) {
            // @codeCoverageIgnoreStart
            throw new SupplierNotFoundException($supplier->uuid()->toString());
            // @codeCoverageIgnoreEnd
        }

        $supplierToUpdate->setName($supplier->name()->toString())
            ->setSlug($supplier->slug())
        ;

        $this->_em->flush();
    }

    public function changeDomiciliation(SupplierDomain $supplier): void
    {
        $supplierToUpdate = $this->find($supplier->uuid()->toString());

        if (!$supplierToUpdate instanceof Supplier) {
            // @codeCoverageIgnoreStart
            throw new SupplierNotFoundException($supplier->uuid()->toString());
            // @codeCoverageIgnoreEnd
        }

        $supplierToUpdate->setAddress($supplier->address()->address())
            ->setPostalCode($supplier->address()->postalCode())
            ->setTown($supplier->address()->town())
            ->setCountry($supplier->address()->country())
            ->setPhone($supplier->phone()->toNumber())
            ->setEmail($supplier->email()->toString())
        ;

        $this->_em->flush();
    }

    public function findAllSuppliers(): SupplierCollection
    {
        $suppliers = $this->findAll();
        $collection = new SupplierCollection();

        if ($suppliers === []) {
            throw new NoSupplierRegisteredException();
        }

        foreach ($suppliers as $supplier) {
            $collection->add($supplier->toDomain());
        }

        return $collection;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findBySlug(string $slug): SupplierDomain
    {
        $alias = self::ALIAS;
        $supplier = $this->createQueryBuilder($alias)
            ->where("{$alias}.slug = :slug")
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (!$supplier instanceof Supplier) {
            // @codeCoverageIgnoreStart
            throw new SupplierNotFoundException($slug);
            // @codeCoverageIgnoreEnd
        }

        return $supplier->toDomain();
    }
}
