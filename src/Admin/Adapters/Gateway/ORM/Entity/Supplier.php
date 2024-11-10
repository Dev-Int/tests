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

use Admin\Adapters\Gateway\ORM\Entity\FamilyLog\FamilyLog;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineSupplierRepository;
use Admin\Entities\Supplier\Supplier as SupplierDomain;
use Doctrine\ORM\Mapping as ORM;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\EmailField;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\PhoneField;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: DoctrineSupplierRepository::class)]
#[UniqueEntity('name')]
class Supplier
{
    #[ORM\Id]
    #[ORM\Column(name: 'uuid', type: 'guid')]
    private string $uuid;
    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    private string $name;
    #[ORM\Column(name: 'address', type: 'text')]
    private string $address;
    #[ORM\Column(name: 'postal_code', type: 'string', length: 5)]
    private string $postalCode;
    #[ORM\Column(name: 'city', type: 'string', length: 255)]
    private string $city;
    #[ORM\Column(name: 'country', type: 'string', length: 255)]
    private string $country;
    #[ORM\Column(name: 'phone', type: 'string', length: 12)]
    private string $phone;
    #[ORM\Column(name: 'email', type: 'string', length: 255)]
    private string $email;
    #[ORM\Column(name: 'contact', type: 'string', length: 100)]
    private string $contact;
    #[ORM\Column(name: 'cellphone', type: 'string', length: 12)]
    private string $cellphone;
    #[ORM\ManyToOne(targetEntity: FamilyLog::class)]
    #[ORM\JoinColumn(name: 'familyLog_id', referencedColumnName: 'uuid')]
    private FamilyLog $familyLog;
    #[ORM\Column(name: 'delay_delivery', type: 'integer')]
    private int $delayDelivery;

    /** @var array<int> */
    #[ORM\Column(name: 'order_days', type: 'simple_array')]
    private array $orderDays = [];
    #[ORM\Column(name: 'active', type: 'boolean')]
    private bool $active;
    #[ORM\Column(name: 'slug', type: 'string', length: 255)]
    private string $slug;

    public function fromDomain(SupplierDomain $supplier, FamilyLog $familyLog): self
    {
        $this->uuid = $supplier->uuid()->toString();
        $this->name = $supplier->name()->toString();
        $this->address = $supplier->address()->address();
        $this->postalCode = $supplier->address()->postalCode();
        $this->city = $supplier->address()->city();
        $this->country = $supplier->address()->country();
        $this->phone = $supplier->phone()->toNumber();
        $this->email = $supplier->email()->toString();
        $this->contact = $supplier->contact();
        $this->cellphone = $supplier->cellphone()->toNumber();
        $this->familyLog = $familyLog;
        $this->delayDelivery = $supplier->delayDelivery();
        $this->orderDays = $supplier->orderDays();
        $this->active = $supplier->active();
        $this->slug = $supplier->slug();

        return $this;
    }

    public function toDomain(): SupplierDomain
    {
        return SupplierDomain::create(
            ResourceUuid::fromString($this->uuid),
            NameField::fromString($this->name),
            $this->address,
            $this->postalCode,
            $this->city,
            $this->country,
            PhoneField::fromString($this->phone),
            EmailField::fromString($this->email),
            $this->contact,
            PhoneField::fromString($this->cellphone),
            $this->familyLog->toDomain($this->familyLog->parent()),
            $this->delayDelivery,
            $this->orderDays,
            $this->active
        );
    }

    /**
     * @codeCoverageIgnore
     */
    public function uuid(): string
    {
        return $this->uuid;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function fullAddress(): string
    {
        return \sprintf("%s\n%s %s, %s", $this->address, $this->postalCode, $this->city, $this->country);
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function address(): string
    {
        return $this->address;
    }

    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function postalCode(): string
    {
        return $this->postalCode;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function city(): string
    {
        return $this->city;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function country(): string
    {
        return $this->country;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function phone(): string
    {
        return $this->phone;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function setContact(string $contact): self
    {
        $this->contact = $contact;

        return $this;
    }

    public function contact(): string
    {
        return $this->contact;
    }

    public function setCellphone(string $cellphone): self
    {
        $this->cellphone = $cellphone;

        return $this;
    }

    public function cellphone(): string
    {
        return $this->cellphone;
    }

    public function setFamilyLog(FamilyLog $familyLog): self
    {
        $this->familyLog = $familyLog;

        return $this;
    }

    public function familyLog(): FamilyLog
    {
        return $this->familyLog;
    }

    public function setDelayDelivery(int $delayDelivery): self
    {
        $this->delayDelivery = $delayDelivery;

        return $this;
    }

    public function delayDelivery(): int
    {
        return $this->delayDelivery;
    }

    /**
     * @param array<int> $orderDays
     */
    public function setOrderDays(array $orderDays): self
    {
        $this->orderDays = $orderDays;

        return $this;
    }

    /**
     * @return array<int>
     */
    public function orderDays(): array
    {
        return array_map(static function (int $orderDay) {
            return $orderDay;
        }, $this->orderDays);
    }

    public function active(): bool
    {
        return $this->active;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function slug(): string
    {
        return $this->slug;
    }
}
