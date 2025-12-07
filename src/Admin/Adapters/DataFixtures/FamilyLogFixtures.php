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
use Admin\Tests\Factory\FamilyLogFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class FamilyLogFixtures extends Fixture
{
    public const REFERENCE_PREFIX = 'familyLog_';

    public function load(ObjectManager $manager): void
    {
        /** @var array<string, FamilyLog> $familyLogs */
        $familyLogs = [];

        foreach ($this->getData() as $datum) {
            $parent = $datum['parent'] !== null ? $familyLogs[$datum['parent']] : null;

            $familyLog = FamilyLogFactory::createOne([
                'label' => $datum['label'],
                'parent' => $parent,
            ]);

            $familyLogs[$datum['name']] = $familyLog->_real();
            $this->setReference(self::REFERENCE_PREFIX . $datum['name'], $familyLog->_real());
        }
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
            ['name' => 'non-alimentaire', 'label' => 'Non-alimentaire', 'parent' => null],
            ['name' => 'emballage', 'label' => 'Emballage', 'parent' => 'non-alimentaire'],
            ['name' => 'hygiene', 'label' => 'Hygiène', 'parent' => 'non-alimentaire'],
        ];
    }
}
