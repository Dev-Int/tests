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

namespace Auth\Contracts\Exception;

final class UserNotFound extends \DomainException
{
    public const string MESSAGE = 'User not found.';

    public function __construct(string $uuid)
    {
        parent::__construct(self::MESSAGE . " (UUID: {$uuid})");
    }
}
