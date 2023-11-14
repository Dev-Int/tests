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

class Contact
{
    protected ResourceUuid $uuid;
    protected NameField $name;
    protected ContactAddress $address;
    protected PhoneField $phone;
    protected PhoneField $facsimile;
    protected EmailField $email;
    protected string $contact;
    protected PhoneField $cellphone;
    protected string $slug;

    public function __construct(
        ResourceUuid $uuid,
        NameField $name,
        ContactAddress $address,
        PhoneField $phone,
        PhoneField $facsimile,
        EmailField $email,
        string $contact,
        PhoneField $cellphone
    ) {
        $this->uuid = $uuid;
        $this->name = $name;
        $this->address = $address;
        $this->phone = $phone;
        $this->facsimile = $facsimile;
        $this->email = $email;
        $this->contact = $contact;
        $this->cellphone = $cellphone;
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
