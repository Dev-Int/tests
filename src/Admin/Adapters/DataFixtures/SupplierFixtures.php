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
use Admin\Adapters\Gateway\ORM\Entity\Supplier;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineSupplierRepository;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
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
            $supplier = (new SupplierDataBuilder())
                ->create($faker->company(), $familyLog->toDomain($familyLog->parent()))
                ->withUuid($faker->uuid())
                ->withAddress($faker->streetAddress())
                ->withPostalCode($faker->postcode())
                ->withTown($faker->city())
                ->withPhone($this->getValidPhoneNumber($faker))
                ->withCellphone($this->getValidPhoneNumber($faker))
                ->withEmail($faker->email())
                ->withContact($faker->name())
                ->withDelayDelivery($datum['delayDelivery'])
                ->withOrderDays($datum['orderDays'])
                ->build()
            ;
            $supplierOrm = (new Supplier())->fromDomain($supplier, $familyLog);
            $this->setReference(self::REFERENCE_PREFIX . $datum['reference'], $supplierOrm);

            /** @var DoctrineSupplierRepository $supplierRepository */
            $supplierRepository = $manager->getRepository(Supplier::class);
            $supplierRepository->save($supplier);
        }

        $manager->flush();
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
