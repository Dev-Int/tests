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

final class CompanyDataBuilder implements DataBuilderInterface
{
    private string $name;
    private string $address;
    private string $postalCode;
    private string $town;
    private string $country;
    private string $phone;
    private string $email;
    private string $contact;

    public function create(string $name): self
    {
        $this->name = $name;
        $this->address = '5, rue des Plantes';
        $this->postalCode = '75000';
        $this->town = 'Paris';
        $this->country = 'France';
        $this->phone = '+33297000000';
        $this->email = 'test@test.fr';
        $this->contact = 'Laurent';

        return $this;
    }

    public function withAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function withPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function withTown(string $town): self
    {
        $this->town = $town;

        return $this;
    }

    public function build(): Company
    {
        return Company::create(
            NameField::fromString($this->name),
            ContactAddress::fromString(
                $this->address,
                $this->postalCode,
                $this->town,
                $this->country
            ),
            PhoneField::fromString($this->phone),
            EmailField::fromString($this->email),
            $this->contact
        );
    }
}
