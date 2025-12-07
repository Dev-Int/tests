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

use Admin\Tests\Factory\TaxFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class TaxFixtures extends Fixture
{
    public const REFERENCE_PREFIX = 'tax_';

    public function load(ObjectManager $manager): void
    {
        foreach ($this->getData() as $datum) {
            $tax = TaxFactory::createOne([
                'name' => $datum['name'],
                'rate' => $datum['rate'],
                'slug' => $datum['slug'],
            ]);

            $this->setReference(self::REFERENCE_PREFIX . $datum['slug'], $tax->_real());
        }
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
