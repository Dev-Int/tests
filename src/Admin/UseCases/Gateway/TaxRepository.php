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

namespace Admin\UseCases\Gateway;

use Admin\Entities\Tax\Tax;
use Admin\Entities\Tax\TaxCollection;

interface TaxRepository
{
    public function exists(float $rate): bool;

    public function hasTax(): bool;

    public function save(Tax $tax): void;

    public function findAllTaxes(): TaxCollection;
}
