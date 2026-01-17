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

namespace Auth\Entities\Exception;

use Shared\Entities\Exception\DomainException;

abstract class UserNotFound extends DomainException
{
    public const string MESSAGE = 'User not found.';

    protected function __construct(string $message = self::MESSAGE)
    {
        parent::__construct($message, DomainException::NOT_FOUND_CODE);
    }
}
