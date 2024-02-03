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

namespace Admin\Adapters\Controller\Symfony\Controller\Company\CreateCompany;

use Admin\UseCases\Company\CreateCompany\CreateCompanyRequest;

final readonly class CreateCompanyApiRequest implements CreateCompanyRequest
{
    public function __construct(
        public string $name,
        public string $address,
        public string $postalCode,
        public string $town,
        public string $country,
        public string $phone,
        public string $email,
        public string $contact,
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function address(): string
    {
        return $this->address;
    }

    public function postalCode(): string
    {
        return $this->postalCode;
    }

    public function town(): string
    {
        return $this->town;
    }

    public function country(): string
    {
        return $this->country;
    }

    public function phone(): string
    {
        return $this->phone;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function contact(): string
    {
        return $this->contact;
    }
}
