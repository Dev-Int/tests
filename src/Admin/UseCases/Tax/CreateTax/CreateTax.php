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

namespace Admin\UseCases\Tax\CreateTax;

use Admin\Entities\Exception\Tax\TaxAlreadyExists;
use Admin\Entities\Repository\TaxRepository;
use Admin\Entities\Tax\Tax;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\NameField;

final readonly class CreateTax
{
    public function __construct(private TaxRepository $taxRepository)
    {
    }

    public function execute(CreateTaxRequest $request): CreateTaxResponse
    {
        $isExists = $this->taxRepository->exists($request->name(), $request->rate());
        if ($isExists) {
            throw new TaxAlreadyExists($request->name(), $request->rate());
        }

        $tax = Tax::create(
            ResourceUuid::generate(),
            NameField::fromString($request->name()),
            $request->rate()
        );

        $this->taxRepository->save($tax);

        return new CreateTaxResponse($tax);
    }
}
