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

namespace Admin\Entities\Exception;

use Shared\Entities\Exception\DomainException;

final class InvalidResetUrlException extends DomainException
{
    private const string MESSAGE = 'Invalid reset url.';

    public function __construct()
    {
        parent::__construct(self::MESSAGE, DomainException::INVALID_ARGUMENT_CODE);
    }
}
