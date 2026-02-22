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

namespace Auth\Adapters\Gateway\ORM;

use Auth\UseCases\Gateway\TransactionGateway;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(id: TransactionGateway::class)]
final readonly class DoctrineTransactionAdapter implements TransactionGateway
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function wrapInTransaction(callable $operation): mixed
    {
        return $this->entityManager->wrapInTransaction($operation);
    }
}
