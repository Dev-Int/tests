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

namespace Admin\Tests\Factory;

use Admin\Adapters\Gateway\ORM\Entity\FamilyLog\FamilyLog;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<FamilyLog>
 */
final class FamilyLogFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return FamilyLog::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'label' => self::faker()->words(2, true),
            'uuid' => self::faker()->uuid(),
            'parent' => null,
        ];
    }

    protected function initialize(): static
    {
        return $this->instantiateWith(
            /**
             * @param array{label: string, uuid: string, parent: FamilyLog|null} $attributes
             */
            static function (array $attributes): FamilyLog {
                \assert(\is_string($attributes['label']));
                \assert(\is_string($attributes['uuid']));

                $parentDomain = null;
                if ($attributes['parent'] instanceof FamilyLog) {
                    $parentDomain = $attributes['parent']->toDomain($attributes['parent']->parent());
                }

                $familyLogDomain = (new FamilyLogDataBuilder())
                    ->create($attributes['label'])
                    ->withUuid($attributes['uuid'])
                    ->withParent($parentDomain)
                    ->build()
                ;

                $familyLogOrm = (new FamilyLog())->fromDomain($familyLogDomain);

                // Assigner le parent ORM (fromDomain ne le gère pas)
                if ($attributes['parent'] instanceof FamilyLog) {
                    $familyLogOrm->setParent($attributes['parent']);
                }

                return $familyLogOrm;
            }
        );
    }
}
