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

use Admin\Adapters\Gateway\ORM\Entity\FamilyLog\FamilyLog;
use Admin\Tests\Factory\SupplierFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Shared\Entities\Exception\InvalidPhoneException;
use Shared\Entities\VO\PhoneField;

final class SupplierFixtures extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_PREFIX = 'supplier_';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        foreach ($this->getData() as $datum) {
            $familyLog = $this->getReference($datum['familyLogReference'], FamilyLog::class);

            $supplier = SupplierFactory::createOne([
                'name' => $faker->company(),
                'familyLog' => $familyLog,
                'streetAddress' => $faker->streetAddress(),
                'postalCode' => $faker->postcode(),
                'town' => $faker->city(),
                'phone' => $this->getValidPhoneNumber($faker),
                'cellphone' => $this->getValidPhoneNumber($faker),
                'email' => $faker->email(),
                'contact' => $faker->name(),
                'delayDelivery' => $datum['delayDelivery'],
                'orderDays' => $datum['orderDays'],
            ]);

            $this->setReference(self::REFERENCE_PREFIX . $datum['reference'], $supplier->_real());
        }
    }

    public function getDependencies(): array
    {
        return [
            FamilyLogFixtures::class,
        ];
    }

    /**
     * @return iterable<array{reference: string, familyLogReference: string, delayDelivery: int, orderDays: array<int>}>
     */
    private function getData(): iterable
    {
        return [
            [
                'reference' => 'surgele',
                'familyLogReference' => FamilyLogFixtures::REFERENCE_PREFIX . 'surgele',
                'delayDelivery' => 2,
                'orderDays' => [1, 4],
            ],
            [
                'reference' => 'frais',
                'familyLogReference' => FamilyLogFixtures::REFERENCE_PREFIX . 'frais',
                'delayDelivery' => 3,
                'orderDays' => [2, 5],
            ],
            [
                'reference' => 'epicerie',
                'familyLogReference' => FamilyLogFixtures::REFERENCE_PREFIX . 'epicerie',
                'delayDelivery' => 4,
                'orderDays' => [4],
            ],
            [
                'reference' => 'maraichere',
                'familyLogReference' => FamilyLogFixtures::REFERENCE_PREFIX . 'frais-fruits-legumes',
                'delayDelivery' => 1,
                'orderDays' => [1, 3, 5],
            ],
        ];
    }

    private function getValidPhoneNumber(Generator $faker): string
    {
        try {
            $phoneNumber = PhoneField::fromString($faker->phoneNumber())->toNumber();
        } catch (InvalidPhoneException $e) {
            $phoneNumber = $this->getValidPhoneNumber($faker);
        }

        return $phoneNumber;
    }
}
