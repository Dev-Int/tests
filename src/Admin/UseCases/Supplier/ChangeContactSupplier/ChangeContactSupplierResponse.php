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

namespace Admin\UseCases\Supplier\ChangeContactSupplier;

use Admin\Entities\Supplier\Supplier;

final readonly class ChangeContactSupplierResponse
{
    public function __construct(public Supplier $supplier)
    {
    }
}
