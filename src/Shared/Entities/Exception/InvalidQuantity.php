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

namespace Shared\Entities\Exception;

final class InvalidQuantity extends \DomainException
{
    public const MESSAGE = 'La quantité doit être un nombre.';

    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct(self::MESSAGE, 400, $previous);
    }
}
