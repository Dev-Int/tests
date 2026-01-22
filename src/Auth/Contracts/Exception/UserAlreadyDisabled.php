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

final class UserAlreadyDisabled extends \DomainException
{
    public const string MESSAGE = 'L\'utilisateur est déjà désactivé.';

    public function __construct(string $uuid)
    {
        parent::__construct(\sprintf('%s UUID: %s', self::MESSAGE, $uuid));
    }
}
