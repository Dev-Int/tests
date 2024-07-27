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

use Admin\Adapters\Gateway\ORM\Entity\Company;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

final class CompanyFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $company = new Company(
            $faker->slug,
            $faker->company,
            $faker->streetAddress,
            $faker->postcode,
            $faker->city,
            'France',
            $faker->phoneNumber,
            $faker->email,
            "{$faker->firstName} {$faker->lastName}",
        );
        $manager->persist($company);

        $manager->flush();
    }
}
