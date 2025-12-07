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

namespace Admin\Adapters\Gateway\ORM\Entity;

use Admin\Adapters\Gateway\ORM\Repository\DoctrineCompanyRepository;
use Admin\Entities\Company as CompanyDomain;
use Doctrine\ORM\Mapping as ORM;
use Shared\Entities\VO\ContactAddress;
use Shared\Entities\VO\EmailField;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\PhoneField;

#[ORM\Entity(repositoryClass: DoctrineCompanyRepository::class)]
class Company
{
    #[ORM\Id]
    #[ORM\Column]
    private string $slug;
    #[ORM\Column]
    private string $name;
    #[ORM\Column]
    private string $address;
    #[ORM\Column]
    private string $postalCode;
    #[ORM\Column]
    private string $city;
    #[ORM\Column]
    private string $country;
    #[ORM\Column]
    private string $phone;
    #[ORM\Column]
    private string $email;
    #[ORM\Column]
    private string $contact;

    public static function fromDomain(CompanyDomain $company): self
    {
        return new self(
            $company->slug(),
            $company->name()->toString(),
            $company->address()->address(),
            $company->address()->postalCode(),
            $company->address()->city(),
            $company->address()->country(),
            $company->phone()->toNumber(),
            $company->email()->toString(),
            $company->contact()
        );
    }

    public function __construct(
        string $slug,
        string $name,
        string $address,
        string $postalCode,
        string $city,
        string $country,
        string $phone,
        string $email,
        string $contact
    ) {
        $this->slug = $slug;
        $this->name = $name;
        $this->address = $address;
        $this->postalCode = $postalCode;
        $this->city = $city;
        $this->country = $country;
        $this->phone = $phone;
        $this->email = $email;
        $this->contact = $contact;
    }

    public function slug(): string
    {
        return $this->slug;
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

    public function city(): string
    {
        return $this->city;
    }

    public function country(): string
    {
        return $this->country;
    }

    public function fullAddress(): string
    {
        return $this->address . '<br />' . $this->postalCode . ' ' . $this->city . ', ' . $this->country;
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

    public function update(CompanyDomain $company): void
    {
        $this->address = $company->address()->address();
        $this->postalCode = $company->address()->postalCode();
        $this->city = $company->address()->city();
        $this->country = $company->address()->country();
        $this->phone = $company->phone()->toNumber();
        $this->email = $company->email()->toString();
        $this->contact = $company->contact();
    }

    public function toDomain(): CompanyDomain
    {
        return CompanyDomain::create(
            NameField::fromString($this->name),
            ContactAddress::fromString(
                $this->address,
                $this->postalCode,
                $this->city,
                $this->country
            ),
            PhoneField::fromString($this->phone),
            EmailField::fromString($this->email),
            $this->contact
        );
    }
}
