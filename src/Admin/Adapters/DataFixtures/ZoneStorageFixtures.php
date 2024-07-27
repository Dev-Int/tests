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
use Admin\Adapters\Gateway\ORM\Entity\ZoneStorage;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineZoneStorageRepository;
use Admin\Tests\DataBuilder\ZoneStorageDataBuilder;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

final class ZoneStorageFixtures extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_PREFIX = 'zoneStorage_';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        foreach ($this->getData() as $datum) {
            /** @var FamilyLog $familyLog */
            $familyLog = $this->getReference($datum['familyLogReference']);

            $zoneStorage = (new ZoneStorageDataBuilder())
                ->create($datum['label'], $familyLog->toDomain($familyLog->parent()))
                ->withUuid($faker->uuid())
                ->build()
            ;
            $zoneStorageOrm = (new ZoneStorage())->fromDomain($zoneStorage, $familyLog);
            $this->setReference(self::REFERENCE_PREFIX . $datum['slug'], $zoneStorageOrm);

            /** @var DoctrineZoneStorageRepository $zoneStorageRepository */
            $zoneStorageRepository = $manager->getRepository(ZoneStorage::class);
            $zoneStorageRepository->save($zoneStorage);
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
     * @return iterable<array{slug: string, label: string, familyLogReference: string}>
     */
    private function getData(): iterable
    {
        return [
            [
                'slug' => 'negative',
                'label' => 'Réserve négative',
                'familyLogReference' => FamilyLogFixtures::REFERENCE_PREFIX . 'surgele',
            ],
            [
                'slug' => 'positive',
                'label' => 'Réserve positive',
                'familyLogReference' => FamilyLogFixtures::REFERENCE_PREFIX . 'frais',
            ],
            [
                'slug' => 'seche',
                'label' => 'Réserve sèche',
                'familyLogReference' => FamilyLogFixtures::REFERENCE_PREFIX . 'epicerie',
            ],
            [
                'slug' => 'maraichere',
                'label' => 'Réserve maraîchère',
                'familyLogReference' => FamilyLogFixtures::REFERENCE_PREFIX . 'frais-fruits-legumes',
            ],
        ];
    }
}
