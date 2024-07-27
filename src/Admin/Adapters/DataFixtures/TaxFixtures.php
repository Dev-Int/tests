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

use Admin\Adapters\Gateway\ORM\Entity\Tax;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

final class TaxFixtures extends Fixture
{
    public const REFERENCE_PREFIX = 'tax_';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        foreach ($this->getData() as $datum) {
            $tax = (new TaxDataBuilder())->create($datum['name'], $datum['rate'])
                ->withUuid($faker->uuid())
                ->build()
            ;
            $taxOrm = (new Tax())->fromDomain($tax);
            $this->setReference(self::REFERENCE_PREFIX . $datum['slug'], $taxOrm);
            $manager->persist($taxOrm);
        }

        $manager->flush();
    }

    /**
     * @return iterable<array{slug: string, name: string, rate: float}>
     */
    private function getData(): iterable
    {
        return [
            ['slug' => 'taux-normal', 'name' => 'TVA taux normal', 'rate' => 20.0],
            ['slug' => 'taux-intermediaire', 'name' => 'TVA taux intermédiaire', 'rate' => 10.0],
            ['slug' => 'taux-reduit', 'name' => 'TVA taux réduit', 'rate' => 5.5],
            ['slug' => 'taux-particulier', 'name' => 'TVA taux particulier', 'rate' => 2.1],
        ];
    }
}
