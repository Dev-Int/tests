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
final class Company
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
    private string $town;
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
            $company->address()->town(),
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
        string $town,
        string $country,
        string $phone,
        string $email,
        string $contact
    ) {
        $this->slug = $slug;
        $this->name = $name;
        $this->address = $address;
        $this->postalCode = $postalCode;
        $this->town = $town;
        $this->country = $country;
        $this->phone = $phone;
        $this->email = $email;
        $this->contact = $contact;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function address(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function postalCode(): string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function town(): string
    {
        return $this->town;
    }

    public function setTown(string $town): void
    {
        $this->town = $town;
    }

    public function country(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public function fullAddress(): string
    {
        return $this->address . '<br />' . $this->postalCode . ' ' . $this->town . ', ' . $this->country;
    }

    public function phone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function contact(): string
    {
        return $this->contact;
    }

    public function setContact(string $contact): void
    {
        $this->contact = $contact;
    }

    public function update(CompanyDomain $company): void
    {
        $this->address = $company->address()->address();
        $this->postalCode = $company->address()->postalCode();
        $this->town = $company->address()->town();
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
                $this->town,
                $this->country
            ),
            PhoneField::fromString($this->phone),
            EmailField::fromString($this->email),
            $this->contact
        );
    }
}
