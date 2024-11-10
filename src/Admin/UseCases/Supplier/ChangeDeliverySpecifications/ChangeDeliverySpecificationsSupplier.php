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

namespace Admin\UseCases\Supplier\ChangeDeliverySpecifications;

use Admin\UseCases\Gateway\SupplierRepository;

final readonly class ChangeDeliverySpecificationsSupplier
{
    public function __construct(private SupplierRepository $supplierRepository)
    {
    }

    public function execute(
        ChangeDeliverySpecificationsSupplierRequest $request
    ): ChangeDeliverySpecificationsSupplierResponse {
        $supplier = $this->supplierRepository->findBySlug($request->slug());

        $supplier->changeDeliverySpecifications(
            $request->familyLog(),
            $request->delayDelivery(),
            $request->orderDays()
        );

        $this->supplierRepository->changeDeliverySpecifications($supplier);

        return new ChangeDeliverySpecificationsSupplierResponse($supplier);
    }
}
