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
use Admin\Entities\Exception\Company\CompanyNotFound;
use Admin\Entities\Exception\Company\NoCompanyRegistered;
use Admin\Entities\Repository\CompanyRepository;
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
        $this->getEntityManager()->persist(Company::fromDomain($company));
        $this->getEntityManager()->flush();
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
            // @codeCoverageIgnoreStart
            throw new UnexpectedResultException('Integer expected!');
            // @codeCoverageIgnoreEnd
        }

        return $count > 0;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getByName(string $name): CompanyDomain
    {
        $alias = self::ALIAS;
        $company = $this->createQueryBuilder($alias)
            ->where("{$alias}.name = :name")
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (!$company instanceof Company) {
            // @codeCoverageIgnoreStart
            throw new CompanyNotFound($name);
            // @codeCoverageIgnoreEnd
        }

        return $company->toDomain();
    }

    public function update(CompanyDomain $company): void
    {
        $companyToUpdate = $this->find($company->slug());

        if (!$companyToUpdate instanceof Company) {
            // @codeCoverageIgnoreStart
            throw new CompanyNotFound($company->name()->toString());
            // @codeCoverageIgnoreEnd
        }

        $companyToUpdate->update($company);

        $this->getEntityManager()->flush();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoCompanyRegistered
     */
    public function getCompany(): Company
    {
        $company = $this->createQueryBuilder(self::ALIAS)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (!$company instanceof Company) {
            throw new NoCompanyRegistered();
        }

        return $company;
    }
}
