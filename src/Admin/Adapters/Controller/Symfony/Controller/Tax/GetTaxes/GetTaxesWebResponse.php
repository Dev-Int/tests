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

namespace Admin\Adapters\Controller\Symfony\Controller\Tax\GetTaxes;

use Admin\UseCases\Tax\GetTaxes\GetTaxesResponse;

final class GetTaxesWebResponse
{
    /** @var array<TaxDto> */
    private array $taxes = [];

    public function __construct(GetTaxesResponse $response)
    {
        foreach ($response->taxes as $tax) {
            $this->taxes[] = new TaxDto(
                $tax->uuid()->toString(),
                $tax->name()->toString(),
                $tax->rate()
            );
        }
    }

    /**
     * @return array<TaxDto>
     */
    public function taxes(): array
    {
        return $this->taxes;
    }
}
