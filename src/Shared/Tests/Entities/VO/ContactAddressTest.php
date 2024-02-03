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

/**
 * @group unitTest
 */
final class ContactAddressTest extends TestCase
{
    public function testInstantiateContactAddress(): void
    {
        // Arrange & Act
        $address = ContactAddress::fromString(
            '2, rue de la truite',
            '75000',
            'Paris',
            'France'
        );

        // Assert
        self::assertSame('2, rue de la truite', $address->address());
        self::assertSame('75000', $address->postalCode());
        self::assertSame('Paris', $address->town());
        self::assertSame('France', $address->country());
        self::assertSame(
            "2, rue de la truite\n75000 Paris, France",
            $address->getFullAddress()
        );
    }
}
