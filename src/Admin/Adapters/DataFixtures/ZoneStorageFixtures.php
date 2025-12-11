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
use Admin\Tests\Factory\ZoneStorageFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class ZoneStorageFixtures extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_PREFIX = 'zoneStorage_';

    public function load(ObjectManager $manager): void
    {
        foreach ($this->getData() as $datum) {
            $familyLog = $this->getReference($datum['familyLogReference'], FamilyLog::class);

            $zoneStorage = ZoneStorageFactory::createOne([
                'label' => $datum['label'],
                'familyLog' => $familyLog,
                'slug' => $datum['slug'],
            ]);

            $this->setReference(self::REFERENCE_PREFIX . $datum['slug'], $zoneStorage->_real());
        }
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
