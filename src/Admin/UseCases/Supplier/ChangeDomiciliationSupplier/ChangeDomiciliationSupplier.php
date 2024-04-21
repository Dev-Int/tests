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

namespace Admin\UseCases\Supplier\ChangeDomiciliationSupplier;

use Admin\UseCases\Gateway\SupplierRepository;
use Shared\Entities\VO\ContactAddress;
use Shared\Entities\VO\EmailField;
use Shared\Entities\VO\PhoneField;

final readonly class ChangeDomiciliationSupplier
{
    public function __construct(private SupplierRepository $supplierRepository)
    {
    }

    public function execute(ChangeDomiciliationSupplierRequest $request): ChangeDomiciliationSupplierResponse
    {
        $supplier = $this->supplierRepository->findBySlug($request->slug());

        $supplier->changeDomiciliation(
            ContactAddress::fromString(
                $request->address(),
                $request->postalCode(),
                $request->town(),
                $request->country()
            ),
            PhoneField::fromString($request->phone()),
            EmailField::fromString($request->email())
        );

        $this->supplierRepository->changeDomiciliation($supplier);

        return new ChangeDomiciliationSupplierResponse($supplier);
    }
}
