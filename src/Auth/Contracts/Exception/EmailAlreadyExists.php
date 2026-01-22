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

final class EmailAlreadyExists extends \DomainException
{
    public const string MESSAGE = 'Un utilisateur avec cet email existe déjà.';

    public function __construct(string $email)
    {
        parent::__construct(\sprintf('%s Email: %s', self::MESSAGE, $email));
    }
}
