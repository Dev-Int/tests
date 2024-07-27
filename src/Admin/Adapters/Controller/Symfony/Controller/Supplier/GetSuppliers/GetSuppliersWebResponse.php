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

namespace Admin\Adapters\Controller\Symfony\Controller\Supplier\GetSuppliers;

use Admin\UseCases\Supplier\GetSuppliers\GetSuppliersResponse;

final class GetSuppliersWebResponse
{
    /** @var array<SupplierDto> */
    private array $suppliers = [];
    private int $totalItems;

    public function __construct(GetSuppliersResponse $response)
    {
        foreach ($response->suppliers as $supplier) {
            $this->suppliers[] = new SupplierDto(
                $supplier->uuid()->toString(),
                $supplier->name()->toString(),
                $supplier->slug()
            );
        }

        $this->totalItems = $response->suppliers->count();
    }

    /**
     * @return array<SupplierDto>
     */
    public function suppliers(): array
    {
        return $this->suppliers;
    }

    public function totalItems(): int
    {
        return $this->totalItems;
    }
}
