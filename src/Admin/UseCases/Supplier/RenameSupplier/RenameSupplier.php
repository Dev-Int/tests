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

namespace Admin\UseCases\Supplier\RenameSupplier;

use Admin\Entities\Exception\Supplier\SupplierAlreadyExists;
use Admin\UseCases\Gateway\SupplierRepository;
use Shared\Entities\VO\NameField;

final readonly class RenameSupplier
{
    public function __construct(private SupplierRepository $supplierRepository)
    {
    }

    public function execute(RenameSupplierRequest $request): RenameSupplierResponse
    {
        $isExists = $this->supplierRepository->exists($request->name());
        if ($isExists) {
            throw new SupplierAlreadyExists($request->name());
        }

        $supplier = $this->supplierRepository->findBySlug($request->slug());

        $supplier->rename(NameField::fromString($request->name()));

        $this->supplierRepository->renameSupplier($supplier);

        return new RenameSupplierResponse($supplier);
    }
}
