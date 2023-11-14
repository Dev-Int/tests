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

namespace Shared\Tests\Entities\VO;

use PHPUnit\Framework\TestCase;
use Shared\Entities\VO\ContactAddress;

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
        static::assertSame('2, rue de la truite', $address->address());
        static::assertSame('75000', $address->zipCode());
        static::assertSame('Paris', $address->town());
        static::assertSame('France', $address->country());
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
        static::assertSame(
            '2, rue de la truite
75000 Paris, France',
            $address->getFullAddress()
        );
    }
}
