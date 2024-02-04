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

namespace Admin\Entities;

use Shared\Entities\VO\ContactAddress;
use Shared\Entities\VO\EmailField;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\PhoneField;

final class Company
{
    private string $slug;

    public static function create(
        NameField $name,
        ContactAddress $address,
        PhoneField $phone,
        EmailField $email,
        string $contact
    ): self {
        return new self($name, $address, $phone, $email, $contact);
    }

    private function __construct(
        private NameField $name,
        private ContactAddress $address,
        private PhoneField $phone,
        private EmailField $email,
        private string $contact
    ) {
        $this->slug = $this->name->slugify();
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

    public function email(): EmailField
    {
        return $this->email;
    }

    public function contact(): string
    {
        return $this->contact;
    }

    public function slug(): string
    {
        return $this->slug;
    }
}
