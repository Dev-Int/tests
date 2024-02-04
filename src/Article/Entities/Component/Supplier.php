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

namespace Article\Entities\Component;

use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\ContactAddress;
use Shared\Entities\VO\EmailField;
use Shared\Entities\VO\FamilyLog;
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
        string $zipCode,
        string $town,
        string $country,
        PhoneField $phone,
        PhoneField $facsimile,
        EmailField $email,
        string $contact,
        PhoneField $gsm,
        FamilyLog $familyLog,
        int $delayDelivery,
        array $orderDays,
        bool $active = true
    ): self {
        return new self(
            $uuid,
            $name,
            ContactAddress::fromString($address, $zipCode, $town, $country),
            $phone,
            $facsimile,
            $email,
            $contact,
            $gsm,
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
        private readonly NameField $name,
        private readonly ContactAddress $address,
        private readonly PhoneField $phone,
        private readonly PhoneField $facsimile,
        private readonly EmailField $email,
        private readonly string $contact,
        private readonly PhoneField $cellphone,
        private readonly FamilyLog $familyLog,
        private readonly int $delayDelivery,
        private readonly array $orderDays,
        private readonly bool $active = true
    ) {
        $this->slug = $name->slugify();
    }

    public function uuid(): ResourceUuid
    {
        return $this->uuid;
    }

    public function name(): NameField
    {
        return $this->name;
    }

    public function address(): ContactAddress
    {
        return $this->address;
    }

    public function phone(): PhoneField
    {
        return $this->phone;
    }

    public function facsimile(): PhoneField
    {
        return $this->facsimile;
    }

    public function email(): EmailField
    {
        return $this->email;
    }

    public function contact(): string
    {
        return $this->contact;
    }

    public function cellphone(): PhoneField
    {
        return $this->cellphone;
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

    public function isActive(): bool
    {
        return $this->active;
    }

    public function slug(): string
    {
        return $this->slug;
    }
}
