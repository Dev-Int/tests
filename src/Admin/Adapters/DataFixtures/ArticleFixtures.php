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
use Admin\Adapters\Gateway\ORM\Entity\Tax;
use Admin\Adapters\Gateway\ORM\Entity\ZoneStorage;
use Admin\Tests\Factory\ArticleFactory;
use Admin\Tests\Factory\PackagingPresets;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use FakerRestaurant\Provider\fr_FR\Restaurant;

final class ArticleFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $faker->addProvider(new Restaurant($faker));

        foreach ($this->getData() as $datum) {
            $supplier = $this->getReference($datum['supplierReference'], Supplier::class);
            $familyLog = $this->getReference($datum['familyLogReference'], FamilyLog::class);
            $tax = $this->getReference(TaxFixtures::REFERENCE_PREFIX . 'taux-reduit', Tax::class);
            $zoneStorage = $this->getReference($datum['zoneStorageReference'], ZoneStorage::class);

            $names = $this->getArticleNames($faker, $datum['familyLogReference']);

            // Extrait le slug du familyLog pour choisir le bon preset
            $familyLogSlug = str_replace(FamilyLogFixtures::REFERENCE_PREFIX, '', $datum['familyLogReference']);
            $packaging = PackagingPresets::forCategory($familyLogSlug);

            foreach ($names as $name) {
                ArticleFactory::createOne([
                    'name' => $name,
                    'uuid' => $faker->uuid(),
                    'supplier' => $supplier,
                    'tax' => $tax,
                    'zoneStorages' => [$zoneStorage],
                    'familyLog' => $familyLog,
                    'packaging' => $packaging,
                    'unitPrice' => $faker->numberBetween(100, 99999),
                    'minStock' => $faker->randomFloat(3, 1, 5),
                    'quantity' => $faker->randomFloat(3, 0.5, 15),
                ]);
            }
        }
    }

    public function getDependencies(): array
    {
        return [
            FamilyLogFixtures::class,
            UnitFixtures::class,
            TaxFixtures::class,
            ZoneStorageFixtures::class,
            SupplierFixtures::class,
        ];
    }

    /**
     * @return iterable<array{familyLogReference: string, zoneStorageReference: string, supplierReference: string}>
     */
    private function getData(): iterable
    {
        return [
            [
                'familyLogReference' => FamilyLogFixtures::REFERENCE_PREFIX . 'surgele',
                'zoneStorageReference' => ZoneStorageFixtures::REFERENCE_PREFIX . 'negative',
                'supplierReference' => SupplierFixtures::REFERENCE_PREFIX . 'surgele',
            ],
            [
                'familyLogReference' => FamilyLogFixtures::REFERENCE_PREFIX . 'frais',
                'zoneStorageReference' => ZoneStorageFixtures::REFERENCE_PREFIX . 'positive',
                'supplierReference' => SupplierFixtures::REFERENCE_PREFIX . 'frais',
            ],
            [
                'familyLogReference' => FamilyLogFixtures::REFERENCE_PREFIX . 'epicerie',
                'zoneStorageReference' => ZoneStorageFixtures::REFERENCE_PREFIX . 'seche',
                'supplierReference' => SupplierFixtures::REFERENCE_PREFIX . 'epicerie',
            ],
            [
                'familyLogReference' => FamilyLogFixtures::REFERENCE_PREFIX . 'frais-fruits-legumes',
                'zoneStorageReference' => ZoneStorageFixtures::REFERENCE_PREFIX . 'maraichere',
                'supplierReference' => SupplierFixtures::REFERENCE_PREFIX . 'maraichere',
            ],
        ];
    }

    /**
     * @return array<string>
     */
    private function getArticleNames(Generator $faker, string $familyLogReference): array
    {
        $articleNames = [];
        $articleTypes = ['fruit', 'vegetable', 'meat', 'dairy'];
        $articleMaraicherTypes = ['fruit', 'vegetable'];

        if (
            $familyLogReference === FamilyLogFixtures::REFERENCE_PREFIX . 'surgele'
            || $familyLogReference === FamilyLogFixtures::REFERENCE_PREFIX . 'frais'
        ) {
            for ($i = 1; $i <= 20; $i++) {
                $articleType = array_rand(array_flip($articleTypes));

                $articleNames[] = match ($articleType) {
                    'fruit' => $faker->fruitName(),
                    'vegetable' => $faker->vegetableName(),
                    'meat' => $faker->meatName(),
                    'dairy' => $faker->dairyName(),
                };
            }
        }

        if ($familyLogReference === FamilyLogFixtures::REFERENCE_PREFIX . 'frais-fruits-legumes') {
            for ($i = 1; $i <= 20; $i++) {
                $articleType = array_rand(array_flip($articleMaraicherTypes));

                $articleNames[] = match ($articleType) {
                    'fruit' => $faker->fruitName(),
                    'vegetable' => $faker->vegetableName(),
                };
            }
        }

        return $articleNames;
    }
}
