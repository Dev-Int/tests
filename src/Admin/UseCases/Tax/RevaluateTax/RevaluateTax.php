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

namespace Admin\UseCases\Tax\RevaluateTax;

use Admin\Entities\Exception\TaxAlreadyExistsException;
use Admin\UseCases\Gateway\TaxRepository;

final readonly class RevaluateTax
{
    public function __construct(private TaxRepository $taxRepository)
    {
    }

    public function execute(RevaluateTaxRequest $request): RevaluateTaxResponse
    {
        $tax = $this->taxRepository->findById($request->uuid());

        $isExists = $this->taxRepository->exists($tax->name()->toString(), $request->rate());
        if ($isExists) {
            throw new TaxAlreadyExistsException($tax->name()->toString(), $request->rate());
        }

        $tax->revaluate($request->rate());

        $this->taxRepository->revaluate($tax);

        return new RevaluateTaxResponse($tax);
    }
}
