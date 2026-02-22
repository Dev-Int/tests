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

namespace Auth\UseCases\Gateway;

interface TransactionGateway
{
    /**
     * @template T
     *
     * @param callable(): T $operation
     *
     * @return T
     */
    public function wrapInTransaction(callable $operation): mixed;
}
