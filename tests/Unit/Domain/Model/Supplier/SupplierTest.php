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

namespace Tests\Unit\Domain\Model\Supplier;

use Article\Entities\Component\Supplier;
use PHPUnit\Framework\TestCase;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\EmailField;
use Shared\Entities\VO\FamilyLog;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\PhoneField;

class SupplierTest extends TestCase
{
    final public function testInstantiateSupplier(): void
    {
        // Arrange & Act
        $supplier = Supplier::create(
            ResourceUuid::fromString('a136c6fe-8f6e-45ed-91bc-586374791033'),
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
            FamilyLog::create(NameField::fromString('Surgelé')),
            3,
            [1, 3]
        );

        // Assert
        static::assertSame('a136c6fe-8f6e-45ed-91bc-586374791033', $supplier->uuid()->toString());
        static::assertSame('Davigel', $supplier->name()->getValue());
        static::assertSame("15, rue des givrés\n75000 Paris, France", $supplier->address()->getFullAddress());
        static::assertSame('+33100000001', $supplier->phone()->getValue());
        static::assertSame('+33100000002', $supplier->facsimile()->getValue());
        static::assertSame('contact@davigel.fr', $supplier->email()->getValue());
        static::assertSame('David', $supplier->contact());
        static::assertSame('+33600000001', $supplier->cellphone()->getValue());
        static::assertSame('surgele', $supplier->familyLog()->path());
        static::assertSame(3, $supplier->delayDelivery());
        static::assertSame([1, 3], $supplier->orderDays());
    }
}
