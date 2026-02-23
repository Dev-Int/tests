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

namespace Admin\Entities\Exception\Employee;

final class PageOutOfRange extends \OutOfRangeException
{
    public const string MESSAGE = 'La page demandée est hors des limites de la pagination.';

    public function __construct()
    {
        parent::__construct(self::MESSAGE);
    }
}
