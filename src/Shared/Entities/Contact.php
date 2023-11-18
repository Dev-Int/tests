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

namespace Shared\Entities;

use Shared\Entities\VO\ContactAddress;
use Shared\Entities\VO\EmailField;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\PhoneField;

final class Contact
{
    private string $slug;

    public function __construct(
        private readonly ResourceUuid $uuid,
        private NameField $name,
        private ContactAddress $address,
        private PhoneField $phone,
        private PhoneField $facsimile,
        private EmailField $email,
        private string $contact,
        private PhoneField $cellphone
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

    public function renameContact(NameField $name): void
    {
        $this->name = $name;
        $this->slug = $name->slugify();
    }

    public function address(): ContactAddress
    {
        return $this->address;
    }

    public function rewriteAddress(ContactAddress $address): void
    {
        $this->address = $address;
    }

    public function phone(): PhoneField
    {
        return $this->phone;
    }

    public function changePhoneNumber(PhoneField $phone): void
    {
        $this->phone = $phone;
    }

    public function facsimile(): PhoneField
    {
        return $this->facsimile;
    }

    public function changeFacsimileNumber(PhoneField $facsimile): void
    {
        $this->facsimile = $facsimile;
    }

    public function email(): EmailField
    {
        return $this->email;
    }

    public function rewriteEmail(EmailField $email): void
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

    public function cellphone(): PhoneField
    {
        return $this->cellphone;
    }

    public function changeCellphoneNumber(PhoneField $cellphone): void
    {
        $this->cellphone = $cellphone;
    }

    public function slug(): string
    {
        return $this->slug;
    }
}
