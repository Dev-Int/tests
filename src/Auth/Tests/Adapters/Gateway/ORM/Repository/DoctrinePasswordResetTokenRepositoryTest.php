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

namespace Auth\Tests\Adapters\Gateway\ORM\Repository;

use Auth\Adapters\Gateway\ORM\Repository\DoctrinePasswordResetTokenRepository;
use Auth\Entities\ResetPassword;
use Auth\Tests\Factory\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Shared\Entities\ResourceUuid;
use Shared\Tests\BaseFunctionalTestCase;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 *
 * @covers \Auth\Adapters\Gateway\ORM\Repository\DoctrinePasswordResetTokenRepository
 */
final class DoctrinePasswordResetTokenRepositoryTest extends BaseFunctionalTestCase
{
    use Factories;

    private DoctrinePasswordResetTokenRepository $repository;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var DoctrinePasswordResetTokenRepository $repository */
        $repository = self::getContainer()->get(DoctrinePasswordResetTokenRepository::class);
        $this->repository = $repository;

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        $this->entityManager = $entityManager;
    }

    public function testDeleteByUserUuidRemovesAllTokensForUser(): void
    {
        // Arrange
        $userOrm = UserFactory::createOne()->_real();
        $userUuid = ResourceUuid::fromString($userOrm->uuid());

        $tokenString1 = bin2hex(random_bytes(32));
        $tokenString2 = bin2hex(random_bytes(32));
        $userDomain = $userOrm->toDomain();

        $this->repository->create(new ResetPassword(
            id: ResourceUuid::generate(),
            user: $userDomain,
            token: $tokenString1,
            expiresAt: new \DateTimeImmutable('+24 hours'),
            usedAt: null,
        ));
        $this->repository->create(new ResetPassword(
            id: ResourceUuid::generate(),
            user: $userDomain,
            token: $tokenString2,
            expiresAt: new \DateTimeImmutable('+24 hours'),
            usedAt: null,
        ));

        // Act
        $this->repository->deleteByUserUuid($userUuid);
        // Le DQL DELETE bypasse l'identity map → clear pour lecture fraîche
        $this->entityManager->clear();

        // Assert
        self::assertNull($this->repository->findByToken($tokenString1));
        self::assertNull($this->repository->findByToken($tokenString2));
    }

    public function testDeleteByUserUuidDoesNotAffectOtherUsersTokens(): void
    {
        // Arrange
        $user1 = UserFactory::createOne()->_real();
        $user2 = UserFactory::createOne()->_real();
        $userUuid1 = ResourceUuid::fromString($user1->uuid());

        $tokenForUser1 = bin2hex(random_bytes(32));
        $tokenForUser2 = bin2hex(random_bytes(32));

        $this->repository->create(new ResetPassword(
            id: ResourceUuid::generate(),
            user: $user1->toDomain(),
            token: $tokenForUser1,
            expiresAt: new \DateTimeImmutable('+24 hours'),
            usedAt: null,
        ));
        $this->repository->create(new ResetPassword(
            id: ResourceUuid::generate(),
            user: $user2->toDomain(),
            token: $tokenForUser2,
            expiresAt: new \DateTimeImmutable('+24 hours'),
            usedAt: null,
        ));

        // Act — supprime uniquement les tokens de user1
        $this->repository->deleteByUserUuid($userUuid1);
        $this->entityManager->clear();

        // Assert
        self::assertNull($this->repository->findByToken($tokenForUser1));
        self::assertNotNull($this->repository->findByToken($tokenForUser2));
    }
}
