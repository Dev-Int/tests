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

use Admin\Adapters\Gateway\ORM\Entity\Employee;
use Admin\Entities\Employee\Employee as EmployeeDomain;
use Admin\Entities\Employee\EmployeeCollection;
use Admin\Entities\Exception\Employee\EmployeeNotFound;
use Admin\Entities\Exception\Employee\NoEmployeeRegistered;
use Admin\Entities\Repository\EmployeeRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\UnexpectedResultException;
use Doctrine\Persistence\ManagerRegistry;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\EmailField;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

/**
 * @template-extends ServiceEntityRepository<Employee>
 */
#[AsAlias(EmployeeRepository::class)]
final class DoctrineEmployeeRepository extends ServiceEntityRepository implements EmployeeRepository
{
    public const ALIAS = 'employee';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employee::class);
    }

    public function getByUuid(ResourceUuid $uuid): EmployeeDomain
    {
        $employee = $this->find($uuid->toString());

        if (!$employee instanceof Employee) {
            throw new EmployeeNotFound($uuid);
        }

        return $employee->toDomain();
    }

    public function getByEmail(EmailField $email): EmployeeDomain
    {
        $employee = $this->findOneBy(['email' => $email->toString()]);

        if (!$employee instanceof Employee) {
            throw EmployeeNotFound::byEmail($email);
        }

        return $employee->toDomain();
    }

    public function getAllEmployees(): EmployeeCollection
    {
        $employees = $this->findAll();
        $collection = new EmployeeCollection(\count($employees));

        if ($employees === []) {
            throw new NoEmployeeRegistered();
        }

        foreach ($employees as $employee) {
            $collection->add($employee->toDomain());
        }

        return $collection;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function emailExists(EmailField $email): bool
    {
        $alias = self::ALIAS;
        $employee = $this->createQueryBuilder($alias)
            ->where("{$alias}.email = :email")
            ->setParameter('email', $email->toString())
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $employee !== null;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException|UnexpectedResultException
     */
    public function hasEmployees(): bool
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

    public function save(EmployeeDomain $employee): void
    {
        $employeeOrm = (new Employee())->fromDomain($employee);

        $this->getEntityManager()->persist($employeeOrm);
        $this->getEntityManager()->flush();
    }

    public function updateContactInfo(EmployeeDomain $employee): void
    {
        $employeeOrm = $this->find($employee->uuid()->toString());

        if (!$employeeOrm instanceof Employee) {
            // @codeCoverageIgnoreStart
            throw new EmployeeNotFound($employee->uuid());
            // @codeCoverageIgnoreEnd
        }

        $employeeOrm->updateFromDomain($employee);

        $this->getEntityManager()->flush();
    }

    public function updatePosition(EmployeeDomain $employee): void
    {
        $employeeOrm = $this->find($employee->uuid()->toString());

        if (!$employeeOrm instanceof Employee) {
            // @codeCoverageIgnoreStart
            throw new EmployeeNotFound($employee->uuid());
            // @codeCoverageIgnoreEnd
        }

        $employeeOrm->updateFromDomain($employee);

        $this->getEntityManager()->flush();
    }

    public function changeStatus(EmployeeDomain $employee): void
    {
        $employeeOrm = $this->find($employee->uuid()->toString());

        if (!$employeeOrm instanceof Employee) {
            // @codeCoverageIgnoreStart
            throw new EmployeeNotFound($employee->uuid());
            // @codeCoverageIgnoreEnd
        }

        $employeeOrm->updateFromDomain($employee);

        $this->getEntityManager()->flush();
    }

    public function disable(EmployeeDomain $employee): void
    {
        $employeeOrm = $this->find($employee->uuid()->toString());

        if (!$employeeOrm instanceof Employee) {
            // @codeCoverageIgnoreStart
            throw new EmployeeNotFound($employee->uuid());
            // @codeCoverageIgnoreEnd
        }

        $employeeOrm->updateFromDomain($employee);

        $this->getEntityManager()->flush();
    }
}
