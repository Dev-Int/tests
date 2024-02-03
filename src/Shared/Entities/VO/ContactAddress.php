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

namespace Shared\Entities\VO;

final readonly class ContactAddress
{
    public static function fromString(string $address, string $postalCode, string $town, string $country): self
    {
        return new self($address, $postalCode, $town, $country);
    }

    private function __construct(
        private string $address,
        private string $postalCode,
        private string $town,
        private string $country
    ) {
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

    public function getFullAddress(): string
    {
        return $this->address . "\n" . $this->postalCode . ' ' . $this->town . ', ' . $this->country;
    }
}
