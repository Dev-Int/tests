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

namespace Admin\Tests\DataBuilder;

use Admin\Entities\Company;
use Shared\Entities\VO\ContactAddress;
use Shared\Entities\VO\EmailField;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\PhoneField;

final class CompanyDataBuilder
{
    /** @var array<string, string> */
    private array $company = [];

    public function create(string $name): self
    {
        $this->company['name'] = $name;
        $this->company['address'] = '5, rue des Plantes';
        $this->company['postalCode'] = '75000';
        $this->company['town'] = 'Paris';
        $this->company['country'] = 'France';
        $this->company['phone'] = '+33297000000';
        $this->company['email'] = 'test@test.fr';
        $this->company['contact'] = 'Laurent';

        return $this;
    }

    public function withAddress(string $address): self
    {
        $this->company['address'] = $address;

        return $this;
    }

    public function withPostalCode(string $postalCode): self
    {
        $this->company['postalCode'] = $postalCode;

        return $this;
    }

    public function withTown(string $town): self
    {
        $this->company['town'] = $town;

        return $this;
    }

    public function build(): Company
    {
        return Company::create(
            NameField::fromString($this->company['name']),
            ContactAddress::fromString(
                $this->company['address'],
                $this->company['postalCode'],
                $this->company['town'],
                $this->company['country']
            ),
            PhoneField::fromString($this->company['phone']),
            EmailField::fromString($this->company['email']),
            $this->company['contact']
        );
    }
}
