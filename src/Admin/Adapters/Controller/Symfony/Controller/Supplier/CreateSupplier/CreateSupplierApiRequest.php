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

namespace Admin\Adapters\Controller\Symfony\Controller\Supplier\CreateSupplier;

use Admin\Entities\FamilyLog\FamilyLog;
use Admin\UseCases\Supplier\CreateSupplier\CreateSupplierRequest;

final class CreateSupplierApiRequest implements CreateSupplierRequest
{
    /**
     * @param array<int> $orderDays
     */
    public function __construct(
        public string $name,
        public string $address,
        public string $postalCode,
        public string $town,
        public string $country,
        public string $phone,
        public string $email,
        public string $contact,
        public string $cellphone,
        public FamilyLog $familyLog,
        public int $delayDelivery,
        public array $orderDays
    ) {
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

    public function cellphone(): string
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

    public function orderDays(): array
    {
        return $this->orderDays;
    }
}
