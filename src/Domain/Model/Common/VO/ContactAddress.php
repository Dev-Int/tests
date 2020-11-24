<?php

declare(strict_types=1);

namespace Domain\Model\Common\VO;

final class ContactAddress
{
    private string $address;
    private string $zipCode;
    private string $town;
    private string $country;

    public function __construct(string $address, string $zipCode, string $town, string $country)
    {
        $this->address = $address;
        $this->zipCode = $zipCode;
        $this->town = $town;
        $this->country = $country;
    }

    public static function fromString(string $address, string $zipCode, string $town, string $country): self
    {
        return new self($address, $zipCode, $town, $country);
    }

    public function getValue(): string
    {
        return $this->address . "\n" . $this->zipCode . ' ' . $this->town . ', ' . $this->country;
    }
}
