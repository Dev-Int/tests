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

namespace Auth\Adapters\Gateway\ORM\Finder;

use Auth\Adapters\Gateway\ORM\Entity\User as UserOrm;
use Auth\Entities\User;
use Auth\UseCases\Gateway\Finder\UserFinder;
use Doctrine\ORM\EntityManagerInterface;
use Shared\Entities\ResourceUuid;

final readonly class DoctrineUserFinder implements UserFinder
{
    private const string ALIAS = 'users';

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function findByUuid(ResourceUuid|string $uuid): ?User
    {
        $uuidStr = $uuid instanceof ResourceUuid ? $uuid->toString() : $uuid;
        $alias = self::ALIAS;

        $userOrm = $this->entityManager->createQueryBuilder()
            ->select($alias)
            ->from(UserOrm::class, $alias)
            ->where("{$alias}.uuid = :uuid")
            ->setParameter('uuid', $uuidStr)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (!$userOrm instanceof UserOrm) {
            return null;
        }

        return $userOrm->toDomain();
    }

    /**
     * @return iterable<User>
     */
    public function findAllUsers(): iterable
    {
        $alias = self::ALIAS;

        /** @var array<UserOrm> $usersOrm */
        $usersOrm = $this->entityManager->createQueryBuilder()
            ->select($alias)
            ->from(UserOrm::class, $alias)
            ->orderBy("{$alias}.createdAt", 'DESC')
            ->getQuery()
            ->getResult()
        ;

        foreach ($usersOrm as $userOrm) {
            yield $userOrm->toDomain();
        }
    }

    /**
     * @return iterable<User>
     */
    public function findAllUsersPaginated(int $page, int $itemsPerPage): iterable
    {
        $alias = self::ALIAS;

        /** @var array<UserOrm> $usersOrm */
        $usersOrm = $this->entityManager->createQueryBuilder()
            ->select($alias)
            ->from(UserOrm::class, $alias)
            ->orderBy("{$alias}.createdAt", 'DESC')
            ->setFirstResult(($page - 1) * $itemsPerPage)
            ->setMaxResults($itemsPerPage)
            ->getQuery()
            ->getResult()
        ;

        foreach ($usersOrm as $userOrm) {
            yield $userOrm->toDomain();
        }
    }

    public function countAll(): int
    {
        $alias = self::ALIAS;

        return (int) $this->entityManager->createQueryBuilder()
            ->select("COUNT({$alias}.uuid)")
            ->from(UserOrm::class, $alias)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @return iterable<User>
     */
    public function findActiveUsers(): iterable
    {
        $alias = self::ALIAS;

        /** @var array<UserOrm> $usersOrm */
        $usersOrm = $this->entityManager->createQueryBuilder()
            ->select($alias)
            ->from(UserOrm::class, $alias)
            ->where("{$alias}.disabledAt IS NULL")
            ->orderBy("{$alias}.createdAt", 'DESC')
            ->getQuery()
            ->getResult()
        ;

        foreach ($usersOrm as $userOrm) {
            yield $userOrm->toDomain();
        }
    }
}
