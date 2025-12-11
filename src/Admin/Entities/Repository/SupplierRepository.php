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

use Admin\Entities\Exception\FamilyLog\FamilyLogNotFound;
use Admin\Entities\Exception\Supplier\NoSupplierRegistered;
use Admin\Entities\Exception\Supplier\SupplierNotFound;
use Admin\Entities\Supplier\Supplier;
use Admin\Entities\Supplier\SupplierCollection;

interface SupplierRepository
{
    public function exists(string $name): bool;

    public function hasSupplier(): bool;

    /**
     * @throws FamilyLogNotFound
     */
    public function save(Supplier $supplier): void;

    /**
     * @throws SupplierNotFound
     */
    public function renameSupplier(Supplier $supplier): void;

    /**
     * @throws SupplierNotFound
     */
    public function changeDomiciliation(Supplier $supplier): void;

    /**
     * @throws SupplierNotFound
     */
    public function changeContact(Supplier $supplier): void;

    /**
     * @throws FamilyLogNotFound|SupplierNotFound
     */
    public function changeDeliverySpecifications(Supplier $supplier): void;

    /**
     * @throws NoSupplierRegistered
     */
    public function getAllSuppliers(): SupplierCollection;

    /**
     * @throws NoSupplierRegistered
     */
    public function getAllSuppliersPaginated(int $page, int $itemPerPage): SupplierCollection;

    /**
     * @throws SupplierNotFound
     */
    public function getBySlug(string $slug): Supplier;
}
