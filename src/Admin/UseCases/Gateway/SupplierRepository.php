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

use Admin\Entities\Supplier\Supplier;
use Admin\Entities\Supplier\SupplierCollection;

interface SupplierRepository
{
    public function exists(string $name): bool;

    public function hasSupplier(): bool;

    public function save(Supplier $supplier): void;

    public function renameSupplier(Supplier $supplier): void;

    public function changeDomiciliation(Supplier $supplier): void;

    public function findAllSuppliers(): SupplierCollection;

    public function findBySlug(string $slug): Supplier;
}
