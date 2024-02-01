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

#[ORM\Entity(repositoryClass: DoctrineCompanyRepository::class)]
final class Company
{
    #[ORM\Id]
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
            $company->name()->toString(),
            $company->address()->address(),
            $company->address()->zipCode(),
            $company->address()->town(),
            $company->address()->country(),
            $company->phone()->toNumber(),
            $company->email()->toString(),
            $company->contact()
        );
    }

    public function __construct(
        string $name,
        string $address,
        string $postalCode,
        string $town,
        string $country,
        string $phone,
        string $email,
        string $contact
    ) {
        $this->name = $name;
        $this->address = $address;
        $this->postalCode = $postalCode;
        $this->town = $town;
        $this->country = $country;
        $this->phone = $phone;
        $this->email = $email;
        $this->contact = $contact;
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
