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

use Admin\Adapters\Gateway\ORM\Entity\Company;
use Admin\Entities\Company as CompanyDomain;
use Admin\Entities\Exception\CompanyNotFoundException;
use Admin\Entities\Exception\NoCompanyRegisteredException;
use Admin\UseCases\Gateway\CompanyRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\UnexpectedResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template-extends ServiceEntityRepository<Company>
 */
final class DoctrineCompanyRepository extends ServiceEntityRepository implements CompanyRepository
{
    public const ALIAS = 'company';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Company::class);
    }

    public function save(CompanyDomain $company): void
    {
        $this->_em->persist(Company::fromDomain($company));
        $this->_em->flush();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException|UnexpectedResultException
     */
    public function hasCompany(): bool
    {
        $alias = self::ALIAS;
        $count = $this->createQueryBuilder($alias)
            ->select("COUNT({$alias}.name)")
            ->getQuery()
            ->getSingleScalarResult()
        ;

        if (!\is_int($count)) {
            throw new UnexpectedResultException();
        }

        return $count > 0;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findByName(string $name): CompanyDomain
    {
        $alias = self::ALIAS;
        $company = $this->createQueryBuilder($alias)
            ->where("{$alias}.name = :name")
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (!$company instanceof Company) {
            throw new CompanyNotFoundException($name);
        }

        return $company->toDomain();
    }

    public function update(CompanyDomain $company): void
    {
        $companyToUpdate = $this->find($company->slug());
        if (!$companyToUpdate instanceof Company) {
            throw new CompanyNotFoundException($company->name()->toString());
        }

        $companyToUpdate->update($company);

        $this->_em->flush();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoCompanyRegisteredException
     */
    public function findCompany(): Company
    {
        $company = $this->createQueryBuilder(self::ALIAS)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (!$company instanceof Company) {
            throw new NoCompanyRegisteredException();
        }

        return $company;
    }
}
