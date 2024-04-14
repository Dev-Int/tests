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

namespace Article\Tests\DataBuilder;

use Article\Entities\Component\Supplier;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\ContactAddress;
use Shared\Entities\VO\EmailField;
use Shared\Entities\VO\FamilyLog;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\PhoneField;

final class SupplierDataBuilder
{
    public const VALID_UUID = '810bfed7-be03-4b40-a296-0aca7d4b3458';
    private ResourceUuid $uuid;
    private NameField $name;
    private ContactAddress $address;
    private PhoneField $phone;
    private PhoneField $facsimile;
    private EmailField $email;
    private string $contact = 'Jules Caesar';
    private PhoneField $gsm;
    private FamilyLog $familyLog;
    private int $delayDelivery = 3;

    /** @var array<int> */
    private array $orderDays = [1, 4];
    private bool $active = true;

    public function __construct()
    {
        $this->uuid = ResourceUuid::fromString(self::VALID_UUID);
        $this->name = NameField::fromString('Davigel');
        $this->address = ContactAddress::fromString('5, rue du gel', '75000', 'Paris', 'France');
        $this->phone = PhoneField::fromString('0175000000');
        $this->facsimile = PhoneField::fromString('0175000001');
        $this->email = EmailField::fromString('contact@davigel.fr');
        $this->gsm = PhoneField::fromString('0900000000');
        $this->familyLog = FamilyLog::create(NameField::fromString('Alimentaire'));
    }

    public function build(): Supplier
    {
        return Supplier::create(
            $this->uuid,
            $this->name,
            $this->address->address(),
            $this->address->postalCode(),
            $this->address->town(),
            $this->address->country(),
            $this->phone,
            $this->facsimile,
            $this->email,
            $this->contact,
            $this->gsm,
            $this->familyLog,
            $this->delayDelivery,
            $this->orderDays,
            $this->active
        );
    }
}
