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

namespace Admin\UseCases\Tax\CreateTax;

use Admin\Entities\Tax\Tax;

final readonly class CreateTaxResponse
{
    public function __construct(public Tax $tax)
    {
    }
}
