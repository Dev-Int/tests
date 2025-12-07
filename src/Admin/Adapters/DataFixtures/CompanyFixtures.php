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

use Admin\Tests\Factory\CompanyFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class CompanyFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        CompanyFactory::createOne([
            'name' => 'Dev-Int Création',
        ]);
    }
}
