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
use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

final class FamilyLogFixtures extends Fixture
{
    public const REFERENCE_PREFIX = 'familyLog_';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        /** @var array<string, FamilyLog> $familyLogs */
        $familyLogs = [];

        foreach ($this->getData() as $datum) {
            $parent = $datum['parent'] !== null ? $familyLogs[$datum['parent']] : null;
            $familyLog = (new FamilyLogDataBuilder())
                ->create($datum['label'])
                ->withUuid($faker->uuid())
                ->withParent($parent !== null ? $parent->toDomain($parent->parent()) : $parent)
                ->build()
            ;

            $familyLogOrm = (new FamilyLog())->fromDomain($familyLog);
            $familyLogs[$datum['name']] = $familyLogOrm;
            $this->setReference(self::REFERENCE_PREFIX . $datum['name'], $familyLogOrm);

            /** @var DoctrineFamilyLogRepository $familyLogRepository */
            $familyLogRepository = $manager->getRepository(FamilyLog::class);
            $familyLogRepository->save($familyLog);
        }

        $manager->flush();
    }

    /**
     * @return iterable<array{name: string, label: string, parent: string|null}>
     */
    private function getData(): iterable
    {
        return [
            ['name' => 'alimentaire', 'label' => 'Alimentaire', 'parent' => null],
            ['name' => 'surgele', 'label' => 'Surgelé', 'parent' => 'alimentaire'],
            ['name' => 'surgele-viande', 'label' => 'Viande', 'parent' => 'surgele'],
            ['name' => 'surgele-fruits-legumes', 'label' => 'Fruits & Légumes', 'parent' => 'surgele'],
            ['name' => 'frais', 'label' => 'Frais', 'parent' => 'alimentaire'],
            ['name' => 'frais-viande', 'label' => 'Viande', 'parent' => 'frais'],
            ['name' => 'frais-fruits-legumes', 'label' => 'Fruits & Légumes', 'parent' => 'frais'],
            ['name' => 'epicerie', 'label' => 'Épicerie', 'parent' => 'alimentaire'],
            ['name' => 'non-alimentaire', 'label' => 'Non alimentaire', 'parent' => null],
            ['name' => 'emballage', 'label' => 'Emballage', 'parent' => 'non-alimentaire'],
            ['name' => 'hygiene', 'label' => 'Hygiène', 'parent' => 'non-alimentaire'],
        ];
    }
}
