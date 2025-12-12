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

use Admin\Entities\Repository\SupplierRepository;
use Shared\Entities\VO\PhoneField;

final readonly class ChangeContactSupplier
{
    public function __construct(private SupplierRepository $supplierRepository)
    {
    }

    public function execute(ChangeContactSupplierRequest $request): ChangeContactSupplierResponse
    {
        $supplier = $this->supplierRepository->getBySlug($request->slug());

        $supplier->changeContact($request->contact(), PhoneField::fromString($request->cellphone()));

        $this->supplierRepository->changeContact($supplier);

        return new ChangeContactSupplierResponse($supplier);
    }
}
