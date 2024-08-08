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

use Admin\UseCases\Supplier\GetSuppliers\GetSuppliersRequest;

final class GetSuppliersApiRequest implements GetSuppliersRequest
{
    public function __construct(public int $page, public int $itemsPerPage)
    {
    }

    public function page(): int
    {
        return $this->page;
    }

    public function itemsPerPage(): int
    {
        return $this->itemsPerPage;
    }
}
