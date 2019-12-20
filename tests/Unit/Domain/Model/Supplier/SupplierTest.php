<?php

namespace Tests\Unit\Domain\Model\Supplier;

use Domain\Model\Common\VO\EmailField;
use Domain\Model\Common\VO\NameField;
use Domain\Model\Common\VO\PhoneField;
use Domain\Model\Supplier\Supplier;
use PHPUnit\Framework\TestCase;

class SupplierTest extends TestCase
{
    final public function testInstantiateSupplier(): void
    {
        // Arrange & Act
        $supplier = Supplier::create(
            NameField::fromString('Davigel'),
            '15, rue des givrés',
            '75000',
            'Paris',
            'France',
            PhoneField::fromString('+33100000001'),
            PhoneField::fromString('+33100000002'),
            EmailField::fromString('contact@davigel.fr'),
            'David',
            PhoneField::fromString('+33600000001'),
            'Surgelé',
            3,
            [1, 3]
        );

        // Assert
        $this->assertEquals(
            new Supplier(
                NameField::fromString('Davigel'),
                '15, rue des givrés',
                '75000',
                'Paris',
                'France',
                PhoneField::fromString('+33100000001'),
                PhoneField::fromString('+33100000002'),
                EmailField::fromString('contact@davigel.fr'),
                'David',
                PhoneField::fromString('+33600000001'),
                'Surgelé',
                3,
                [1, 3],
            ),
            $supplier
        );
    }

    final public function testRenameSupplier(): void
    {
        // Arrange
        $supplier = Supplier::create(
            NameField::fromString('Davigel'),
            '15, rue des givrés',
            '75000',
            'Paris',
            'France',
            PhoneField::fromString('+33100000001'),
            PhoneField::fromString('+33100000002'),
            EmailField::fromString('contact@davigel.fr'),
            'David',
            PhoneField::fromString('+33600000001'),
            'Surgelé',
            3,
            [1, 3]
        );

        // Act
        $supplier->renameSupplier(NameField::fromString('Trans Gourmet'));

        // Assert
        $this->assertEquals(
            new Supplier(
                NameField::fromString('Trans Gourmet'),
                '15, rue des givrés',
                '75000',
                'Paris',
                'France',
                PhoneField::fromString('+33100000001'),
                PhoneField::fromString('+33100000002'),
                EmailField::fromString('contact@davigel.fr'),
                'David',
                PhoneField::fromString('+33600000001'),
                'Surgelé',
                3,
                [1, 3],
            ),
            $supplier
        );
    }
}