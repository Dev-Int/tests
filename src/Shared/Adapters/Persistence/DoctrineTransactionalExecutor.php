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

namespace Shared\Adapters\Persistence;

use Doctrine\ORM\EntityManagerInterface;
use Shared\Entities\Persistence\TransactionalExecutorInterface;

/**
 * Doctrine implementation of transactional executor.
 *
 * Uses Doctrine's wrapInTransaction to ensure atomicity.
 */
final readonly class DoctrineTransactionalExecutor implements TransactionalExecutorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function execute(callable $operation): mixed
    {
        return $this->entityManager->wrapInTransaction($operation);
    }
}
