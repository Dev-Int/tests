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

namespace App\Shared\Tests\Entities;

use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Shared\Entities\Exception\InvalidCollectionIterationException;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\NameField;
use Shared\Tests\Entities\Collection\Something;
use Shared\Tests\Entities\Collection\SomethingCollection;

/**
 * @group unitTest
 */
final class CollectionTest extends TestCase
{
    public function testCollection(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $collection = new SomethingCollection();
        $labels = ['label1', 'label2', 'label3'];

        for ($i = 0; $i < 3; $i++) {
            $collection->add(
                new Something(
                    ResourceUuid::fromString($faker->uuid()),
                    NameField::fromString($labels[$i])
                )
            );
        }

        // Act && Assert
        $something1 = $collection->current();
        self::assertSame('label1', $something1->label->toString());

        $collection->next();
        $something2 = $collection->current();
        self::assertSame('label2', $something2->label->toString());

        $collection->next();
        $something3 = $collection->current();
        self::assertSame('label3', $something3->label->toString());
    }

    public function testCollectionFail(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $collection = new SomethingCollection();
        $labels = ['label1', 'label2'];

        for ($i = 0; $i < 2; $i++) {
            $collection->add(
                new Something(
                    ResourceUuid::fromString($faker->uuid()),
                    NameField::fromString($labels[$i])
                )
            );
        }

        // Act && Assert
        $something1 = $collection->current();
        self::assertSame('label1', $something1->label->toString());

        $collection->next();
        $something2 = $collection->current();
        self::assertSame('label2', $something2->label->toString());

        $collection->next();
        $this->expectException(InvalidCollectionIterationException::class);
        $collection->current();
    }
}
