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

namespace Admin\Entities\Employee;

use Shared\Entities\VO\EmailField;
use Shared\Entities\VO\PhoneField;

final readonly class ContactInformation
{
    public static function fromFields(EmailField $email, PhoneField $phone): self
    {
        return new self($email, $phone);
    }

    private function __construct(
        private EmailField $email,
        private PhoneField $phone
    ) {
    }

    public function email(): EmailField
    {
        return $this->email;
    }

    public function phone(): PhoneField
    {
        return $this->phone;
    }

    public function equals(self $other): bool
    {
        return $this->email->toString() === $other->email->toString()
            && $this->phone->toNumber() === $other->phone->toNumber();
    }
}
