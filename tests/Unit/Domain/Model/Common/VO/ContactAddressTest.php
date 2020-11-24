<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Model\Common\VO;

use Domain\Model\Common\VO\ContactAddress;
use PHPUnit\Framework\TestCase;

class ContactAddressTest extends TestCase
{
    final public function testInstantiateContactAddress(): void
    {
        // Arrange & Act
        $address = ContactAddress::fromString(
            '2, rue de la truite',
            '75000',
            'Paris',
            'France'
        );

        // Assert
        self::assertEquals(
            new ContactAddress(
                '2, rue de la truite',
                '75000',
                'Paris',
                'France'
            ),
            $address
        );
    }

    final public function testGetValueOfContactAddress(): void
    {
        // Arrange && Act
        $address = ContactAddress::fromString(
            '2, rue de la truite',
            '75000',
            'Paris',
            'France'
        );

        // Assert
        self::assertEquals(
            '2, rue de la truite
75000 Paris, France',
            $address->getValue()
        );
    }
}
