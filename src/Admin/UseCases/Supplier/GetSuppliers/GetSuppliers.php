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

namespace Admin\UseCases\Supplier\GetSuppliers;

use Admin\UseCases\Gateway\SupplierRepository;

final readonly class GetSuppliers
{
    public function __construct(private SupplierRepository $repository)
    {
    }

    public function execute(): GetSuppliersResponse
    {
        $suppliers = $this->repository->findAllSupplier();

        return new GetSuppliersResponse($suppliers);
    }
}
