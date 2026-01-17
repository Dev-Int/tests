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

use Auth\Adapters\Gateway\ORM\Entity\User;
use Auth\Entities\Exception\UserNotFoundByEmail;
use Auth\Entities\Exception\UserNotFoundById;
use Auth\Entities\Repository\UserRepository;
use Auth\Entities\User as UserDomain;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\EmailField;

/**
 * @extends ServiceEntityRepository<User>
 */
final class DoctrineUserRepository extends ServiceEntityRepository implements UserRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function getByUuid(ResourceUuid $uuid): UserDomain
    {
        $userOrm = $this->find($uuid->toString());
        if (!$userOrm instanceof User) {
            throw new UserNotFoundById($uuid);
        }

        return $userOrm->toDomain();
    }

    public function getByEmail(EmailField $email): UserDomain
    {
        $userOrm = $this->findOneBy(['email' => $email->toString()]);
        if (!$userOrm instanceof User) {
            throw new UserNotFoundByEmail($email);
        }

        return $userOrm->toDomain();
    }

    public function emailExists(EmailField $email): bool
    {
        return $this->findOneBy(['email' => $email->toString()]) !== null;
    }

    public function create(UserDomain $user): void
    {
        $userOrm = User::fromDomain($user);
        $this->getEntityManager()->persist($userOrm);
        $this->getEntityManager()->flush();
    }

    public function update(UserDomain $user): void
    {
        $userOrm = $this->find($user->uuid()->toString());
        if (!$userOrm instanceof User) {
            throw new UserNotFoundById($user->uuid());
        }
        $userOrm->updateFromDomain($user);
        $this->getEntityManager()->flush();
    }

    public function disable(UserDomain $user): void
    {
        $userOrm = $this->find($user->uuid()->toString());
        if (!$userOrm instanceof User) {
            throw new UserNotFoundById($user->uuid());
        }
        $userOrm->updateFromDomain($user);
        $this->getEntityManager()->flush();
    }
}
