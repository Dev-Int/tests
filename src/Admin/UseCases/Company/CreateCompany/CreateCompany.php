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

namespace Admin\UseCases\Company\CreateCompany;

use Admin\Entities\Company;
use Admin\Entities\Exception\CompanyAlreadyExistsException;
use Admin\UseCases\Gateway\CompanyRepository;
use Shared\Entities\VO\ContactAddress;
use Shared\Entities\VO\EmailField;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\PhoneField;

final readonly class CreateCompany
{
    public function __construct(private CompanyRepository $companyRepository)
    {
    }

    public function execute(CreateCompanyRequest $request): CreateCompanyResponse
    {
        $hasCompany = $this->companyRepository->hasCompany();
        if ($hasCompany === true) {
            throw new CompanyAlreadyExistsException($request->name());
        }

        $company = Company::create(
            NameField::fromString($request->name()),
            ContactAddress::fromString(
                $request->address(),
                $request->postalCode(),
                $request->town(),
                $request->country()
            ),
            PhoneField::fromString($request->phone()),
            EmailField::fromString($request->email()),
            $request->contact()
        );

        $this->companyRepository->save($company);

        return new CreateCompanyResponse($company);
    }
}
