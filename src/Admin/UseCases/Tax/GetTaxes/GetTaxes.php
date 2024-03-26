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

namespace Admin\UseCases\Tax\GetTaxes;

use Admin\UseCases\Gateway\TaxRepository;

final readonly class GetTaxes
{
    public function __construct(private TaxRepository $taxRepository)
    {
    }

    public function execute(): GetTaxesResponse
    {
        $taxes = $this->taxRepository->findAllTaxes();

        return new GetTaxesResponse($taxes);
    }
}
