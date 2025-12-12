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

namespace Admin\Entities\Repository;

use Admin\Entities\Exception\Tax\NoTaxRegistered;
use Admin\Entities\Exception\Tax\TaxNotFound;
use Admin\Entities\Tax\Tax;
use Admin\Entities\Tax\TaxCollection;

interface TaxRepository
{
    public function exists(string $name, float $rate): bool;

    public function hasTax(): bool;

    public function save(Tax $tax): void;

    public function rename(Tax $tax): void;

    public function revaluate(Tax $tax): void;

    /**
     * @throws TaxNotFound
     */
    public function getById(string $uuid): Tax;

    /**
     * @throws TaxNotFound
     */
    public function getByName(string $name): Tax;

    /**
     * @throws NoTaxRegistered
     */
    public function getAllTaxes(): TaxCollection;
}
