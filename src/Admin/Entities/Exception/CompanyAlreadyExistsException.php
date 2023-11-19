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

final class CompanyAlreadyExistsException extends \DomainException
{
    public const MESSAGE = 'A company already exists.';

    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct(self::MESSAGE, 400, $previous);
    }
}
