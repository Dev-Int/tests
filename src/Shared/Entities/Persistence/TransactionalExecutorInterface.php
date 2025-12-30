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

namespace Shared\Entities\Persistence;

/**
 * Abstraction for transactional execution of operations.
 *
 * Allows UseCases to wrap operations in a transaction without
 * depending on infrastructure (Doctrine, etc.).
 */
interface TransactionalExecutorInterface
{
    /**
     * Executes the given operation within a transaction.
     *
     * If the operation throws, the transaction is rolled back.
     * If successful, the transaction is committed.
     *
     * @template T
     *
     * @param callable(): T $operation
     *
     * @return T
     *
     * @throws \Throwable If operation fails, transaction is rolled back
     */
    public function execute(callable $operation): mixed;
}
