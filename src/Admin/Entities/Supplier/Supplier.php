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

namespace Admin\Entities\Supplier;

use Admin\Entities\FamilyLog\FamilyLog;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\ContactAddress;
use Shared\Entities\VO\EmailField;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\PhoneField;

final class Supplier
{
    private string $slug;

    /**
     * @param array<int> $orderDays
     */
    public static function create(
        ResourceUuid $uuid,
        NameField $name,
        string $address,
        string $postalCode,
        string $city,
        string $country,
        PhoneField $phone,
        EmailField $email,
        string $contact,
        PhoneField $cellphone,
        FamilyLog $familyLog,
        int $delayDelivery,
        array $orderDays,
        bool $active = true
    ): self {
        return new self(
            $uuid,
            $name,
            ContactAddress::fromString($address, $postalCode, $city, $country),
            $phone,
            $email,
            $contact,
            $cellphone,
            $familyLog,
            $delayDelivery,
            $orderDays,
            $active
        );
    }

    /**
     * @param array<int> $orderDays
     */
    private function __construct(
        private readonly ResourceUuid $uuid,
        private NameField $name,
        private ContactAddress $address,
        private PhoneField $phone,
        private EmailField $email,
        private string $contact,
        private PhoneField $cellphone,
        private FamilyLog $familyLog,
        private int $delayDelivery,
        private array $orderDays,
        private readonly bool $active
    ) {
        $this->slug = $name->slugify();
    }

    public function uuid(): ResourceUuid
    {
        return $this->uuid;
    }

    public function rename(NameField $name): void
    {
        $this->name = $name;
        $this->slug = $name->slugify();
    }

    public function name(): NameField
    {
        return $this->name;
    }

    public function changeDomiciliation(ContactAddress $address, PhoneField $phone, EmailField $email): void
    {
        $this->address = $address;
        $this->phone = $phone;
        $this->email = $email;
    }

    public function address(): ContactAddress
    {
        return $this->address;
    }

    public function phone(): PhoneField
    {
        return $this->phone;
    }

    public function email(): EmailField
    {
        return $this->email;
    }

    public function changeContact(string $contact, PhoneField $cellphone): void
    {
        $this->contact = $contact;
        $this->cellphone = $cellphone;
    }

    public function contact(): string
    {
        return $this->contact;
    }

    public function cellphone(): PhoneField
    {
        return $this->cellphone;
    }

    /**
     * @param array<int> $orderDays
     */
    public function changeDeliverySpecifications(FamilyLog $familyLog, int $delayDelivery, array $orderDays): void
    {
        $this->familyLog = $familyLog;
        $this->delayDelivery = $delayDelivery;
        $this->orderDays = $orderDays;
    }

    public function familyLog(): FamilyLog
    {
        return $this->familyLog;
    }

    public function delayDelivery(): int
    {
        return $this->delayDelivery;
    }

    /**
     * @return array<int>
     */
    public function orderDays(): array
    {
        return $this->orderDays;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function active(): bool
    {
        return $this->active;
    }
}
