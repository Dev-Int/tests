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

namespace Auth\Adapters\Gateway\ORM\Repository;

use Auth\Adapters\Gateway\ORM\Entity\PasswordResetToken as PasswordResetTokenORM;
use Auth\Entities\Repository\PasswordResetTokenRepository;
use Auth\Entities\ResetPassword;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Shared\Entities\ResourceUuid;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

/**
 * @extends ServiceEntityRepository<PasswordResetTokenORM>
 */
#[AsAlias(PasswordResetTokenRepository::class)]
final class DoctrinePasswordResetTokenRepository extends ServiceEntityRepository implements PasswordResetTokenRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordResetTokenORM::class);
    }

    public function create(PasswordResetTokenORM $token): void
    {
        $this->getEntityManager()->persist($token);
        $this->getEntityManager()->flush();
    }

    public function save(ResetPassword $token): void
    {
        $existingToken = $this->findByToken($token->token);
        if (!$existingToken instanceof PasswordResetTokenORM) {
            throw new \RuntimeException('Token ORM entity not found');
        }

        $existingToken->updateFromDomain($token);

        $this->getEntityManager()->persist($existingToken);
        $this->getEntityManager()->flush();
    }

    public function findValidTokenByUserUuid(ResourceUuid $userUuid): ?PasswordResetTokenORM
    {
        $result = $this->createQueryBuilder('prt')
            ->where('prt.userUuid = :userUuid')
            ->andWhere('prt.expiresAt > :now')
            ->andWhere('prt.usedAt IS NULL')
            ->setParameter('userUuid', $userUuid->toString())
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('prt.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
        if (!$result instanceof PasswordResetTokenORM) {
            return null;
        }

        return $result;
    }

    public function findByToken(string $token): ?PasswordResetTokenORM
    {
        return $this->findOneBy(['token' => $token]);
    }

    public function deleteExpiredTokens(): int
    {
        $result = $this->createQueryBuilder('prt')
            ->delete()
            ->where('prt.expiresAt < :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->execute()
        ;
        if (!is_numeric($result)) {
            return 0;
        }

        return (int) $result;
    }
}
