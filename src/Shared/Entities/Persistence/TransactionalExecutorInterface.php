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
 * Abstraction pour l'exécution transactionnelle d'opérations.
 *
 * Permet aux UseCases d'encapsuler des opérations dans une transaction
 * sans dépendre de l'infrastructure (Doctrine, etc.).
 */
interface TransactionalExecutorInterface
{
    /**
     * Exécute l'opération donnée dans une transaction.
     *
     * Si l'opération lève une exception, la transaction est annulée (rollback).
     * Si elle réussit, la transaction est validée (commit).
     *
     * @template T
     *
     * @param callable(): T $operation
     *
     * @return T
     *
     * @throws \Throwable si l'opération échoue, la transaction est annulée
     */
    public function execute(callable $operation): mixed;
}
