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

namespace Admin\UseCases\Tax\RenameTax;

use Admin\Entities\Exception\TaxAlreadyExistsException;
use Admin\UseCases\Gateway\TaxRepository;
use Shared\Entities\VO\NameField;

final readonly class RenameTax
{
    public function __construct(private TaxRepository $taxRepository)
    {
    }

    public function execute(RenameTaxRequest $request): RenameTaxResponse
    {
        $tax = $this->taxRepository->findById($request->uuid());

        $isExists = $this->taxRepository->exists($request->name(), $tax->rate());
        if ($isExists) {
            throw new TaxAlreadyExistsException($request->name(), $tax->rate());
        }

        $tax->rename(NameField::fromString($request->name()));

        $this->taxRepository->rename($tax);

        return new RenameTaxResponse($tax);
    }
}
