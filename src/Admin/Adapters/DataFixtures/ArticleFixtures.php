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

use Admin\Adapters\Gateway\ORM\Entity\Article\Article;
use Admin\Adapters\Gateway\ORM\Entity\FamilyLog\FamilyLog;
use Admin\Adapters\Gateway\ORM\Entity\Supplier;
use Admin\Adapters\Gateway\ORM\Entity\Tax;
use Admin\Adapters\Gateway\ORM\Entity\Unit;
use Admin\Adapters\Gateway\ORM\Entity\ZoneStorage;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineArticleRepository;
use Admin\Entities\Unit\Unit as UnitDomain;
use Admin\Tests\DataBuilder\ArticleDataBuilder;
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
            /** @var Supplier $supplier */
            $supplier = $this->getReference($datum['supplierReference'], Supplier::class);

            /** @var FamilyLog $familyLog */
            $familyLog = $this->getReference($datum['familyLogReference'], FamilyLog::class);

            /** @var Tax $tax */
            $tax = $this->getReference(TaxFixtures::REFERENCE_PREFIX . 'taux-reduit', Tax::class);

            /** @var ZoneStorage $zoneStorage */
            $zoneStorage = $this->getReference($datum['zoneStorageReference'], ZoneStorage::class);

            $names = $this->getArticleNames($faker, $datum['familyLogReference']);

            foreach ($names as $name) {
                $packaging = $this->getPackaging($faker);
                $article = (new ArticleDataBuilder())
                    ->create(
                        $name,
                        $supplier->toDomain(),
                        $tax->toDomain(),
                        [$zoneStorage->toDomain()],
                        $familyLog->toDomain($familyLog->parent()),
                        $packaging
                    )
                    ->withUuid($faker->uuid())
                    ->withAmount($faker->randomNumber(5))
                    ->withMinStock($faker->randomFloat(3, 1, 5))
                    ->withQuantity($faker->randomFloat(3, 0.5, 15))
                    ->build()
                ;

                /** @var DoctrineArticleRepository $articleRepository */
                $articleRepository = $manager->getRepository(Article::class);
                $articleRepository->save($article);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            FamilyLogFixtures::class,
            UnitFixtures::class,
            TaxFixtures::class,
            ZoneStorageFixtures::class,
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

    /**
     * @return array{array{UnitDomain, float}, array{UnitDomain, float}|null, array{UnitDomain, float}|null}
     */
    private function getPackaging(Generator $faker): array
    {
        $units = ['colis', 'kilogramme', 'litre', 'piece', 'boite', 'null'];
        $packaging = [];

        for ($i = 0; $i < 3; $i++) {
            $unitReference = $units[array_rand($units)];
            if ($unitReference === 'null' && $i > 0) {
                $packaging[] = null;

                continue;
            }
            if ($unitReference === 'null' && $i === 0) {
                $unitReference = 'colis';
            }

            /** @var Unit $unit */
            $unit = $this->getReference(UnitFixtures::REFERENCE_PREFIX . $unitReference, Unit::class);
            $quantity = $faker->randomFloat(3, 1, 10);
            $packaging[] = [$unit->toDomain(), $quantity];
        }

        return $packaging;
    }
}
