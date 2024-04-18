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

namespace Admin\Tests\DataBuilder;

use Admin\Adapters\Gateway\ORM\Entity\FamilyLog;
use Admin\Entities\Supplier\Supplier;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\EmailField;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\PhoneField;

final class SupplierDataBuilder implements DataBuilderInterface
{
    public const UUID_VALID = '94d5dbbe-5fd5-42fa-a94d-87091d35f0d0';

    private string $uuid;
    private string $name;
    private string $address = '5, rue des Plantes';
    private string $postalCode = '75000';
    private string $town = 'Paris';
    private string $country = 'France';
    private string $phone = '+33297000000';
    private string $email = 'test@test.fr';
    private string $contact = 'Laurent';
    private string $cellphone = '+33600000000';
    private FamilyLog $familyLog;
    private int $delayDelivery = 3;

    /** @var array<int> */
    private array $orderDays = [1, 4];

    public function create(string $name, FamilyLog $familyLog): self
    {
        $this->uuid = self::UUID_VALID;
        $this->name = $name;
        $this->familyLog = $familyLog;

        return $this;
    }

    public function build(): Supplier
    {
        return Supplier::create(
            ResourceUuid::fromString($this->uuid),
            NameField::fromString($this->name),
            $this->address,
            $this->postalCode,
            $this->town,
            $this->country,
            PhoneField::fromString($this->phone),
            EmailField::fromString($this->email),
            $this->contact,
            PhoneField::fromString($this->cellphone),
            $this->familyLog->toDomain(),
            $this->delayDelivery,
            $this->orderDays
        );
    }
}
