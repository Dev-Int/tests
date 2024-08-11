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

class DomainException extends \DomainException
{
    public const INVALID_ARGUMENT_CODE = 400;
    public const NOT_FOUND_CODE = 404;
    public const CONFLICT_CODE = 409;
    public const BAD_ENTITY_CODE = 422;
}
