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

final class InvalidPasswordResetToken extends DomainException
{
    public const string MESSAGE = 'The password reset token is invalid, expired, already used, or the user is disabled.';

    public function __construct()
    {
        parent::__construct(self::MESSAGE, DomainException::INVALID_ARGUMENT_CODE);
    }
}
