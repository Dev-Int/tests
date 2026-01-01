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

namespace Admin\UseCases\Supplier\CreateSupplier;

use Admin\Entities\Exception\Supplier\SupplierAlreadyExists;
use Admin\Entities\Repository\SupplierRepository;
use Admin\Entities\Supplier\Supplier;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\EmailField;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\PhoneField;

final readonly class CreateSupplier
{
    public function __construct(private SupplierRepository $supplierRepository)
    {
    }

    public function execute(CreateSupplierRequest $request): CreateSupplierResponse
    {
        $isExists = $this->supplierRepository->exists($request->name());
        if ($isExists) {
            throw new SupplierAlreadyExists($request->name());
        }

        $supplier = Supplier::create(
            ResourceUuid::generate(),
            NameField::fromString($request->name()),
            $request->streetAddress(),
            $request->postalCode(),
            $request->city(),
            $request->country(),
            PhoneField::fromString($request->phone()),
            EmailField::fromString($request->email()),
            $request->contact(),
            PhoneField::fromString($request->cellphone()),
            $request->familyLog(),
            $request->delayDelivery(),
            $request->orderDays()
        );

        $this->supplierRepository->save($supplier);

        return new CreateSupplierResponse($supplier);
    }
}
