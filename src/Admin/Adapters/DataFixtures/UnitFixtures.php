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

namespace Admin\Adapters\DataFixtures;

use Admin\Adapters\Gateway\ORM\Entity\Unit;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

final class UnitFixtures extends Fixture
{
    public const REFERENCE_PREFIX = 'unit_';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        foreach ($this->getData() as $datum) {
            $unit = (new UnitDataBuilder())->create($datum['label'], $datum['abbreviation'])
                ->withUuid($faker->uuid())
                ->build()
            ;
            $unitOrm = (new Unit())->fromDomain($unit);
            $this->setReference(self::REFERENCE_PREFIX . $datum['slug'], $unitOrm);
            $manager->persist($unitOrm);
        }

        $manager->flush();
    }

    /**
     * @return iterable<array{slug: string, label: string, abbreviation: string}>
     */
    private function getData(): iterable
    {
        return [
            ['slug' => 'colis', 'label' => 'Colis', 'abbreviation' => 'cls'],
            ['slug' => 'kilogramme', 'label' => 'Kilogramme', 'abbreviation' => 'kg'],
            ['slug' => 'litre', 'label' => 'Litre', 'abbreviation' => 'l'],
            ['slug' => 'piece', 'label' => 'Pièce', 'abbreviation' => 'pce'],
            ['slug' => 'bouteille', 'label' => 'Bouteille', 'abbreviation' => 'btle'],
            ['slug' => 'boite', 'label' => 'Boîte', 'abbreviation' => 'bte'],
        ];
    }
}
