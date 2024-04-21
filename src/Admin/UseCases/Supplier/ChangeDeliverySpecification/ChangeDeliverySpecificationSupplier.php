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

namespace Admin\UseCases\Supplier\ChangeDeliverySpecification;

use Admin\UseCases\Gateway\SupplierRepository;

final readonly class ChangeDeliverySpecificationSupplier
{
    public function __construct(private SupplierRepository $supplierRepository)
    {
    }

    public function execute(
        ChangeDeliverySpecificationSupplierRequest $request
    ): ChangeDeliverySpecificationSupplierResponse {
        $supplier = $this->supplierRepository->findBySlug($request->slug());

        $supplier->changeDeliverySpecification(
            $request->familyLog(),
            $request->delayDelivery(),
            $request->orderDays()
        );

        $this->supplierRepository->changeDeliverySpecification($supplier);

        return new ChangeDeliverySpecificationSupplierResponse($supplier);
    }
}
