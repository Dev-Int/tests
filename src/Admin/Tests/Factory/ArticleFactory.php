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

use Admin\Adapters\Gateway\ORM\Entity\Article\Article;
use Admin\Adapters\Gateway\ORM\Entity\FamilyLog\FamilyLog;
use Admin\Adapters\Gateway\ORM\Entity\Supplier;
use Admin\Adapters\Gateway\ORM\Entity\Tax;
use Admin\Adapters\Gateway\ORM\Entity\ZoneStorage;
use Admin\Tests\DataBuilder\ArticleDataBuilder;
use Doctrine\Common\Collections\ArrayCollection;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Article>
 */
final class ArticleFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Article::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'name' => self::faker()->word(),
            'uuid' => self::faker()->uuid(),
            'supplier' => SupplierFactory::new(),
            'tax' => TaxFactory::new(),
            'zoneStorages' => [ZoneStorageFactory::new()],
            'familyLog' => FamilyLogFactory::new(),
            'packaging' => $this->generateDefaultPackaging(),
            'unitPrice' => self::faker()->numberBetween(100, 10000),
            'minStock' => self::faker()->randomFloat(3, 1, 10),
            'quantity' => self::faker()->randomFloat(3, 0, 20),
        ];
    }

    protected function initialize(): static
    {
        return $this->instantiateWith(
            /**
             * @param array{name: string, uuid: string, supplier: Supplier, tax: Tax, zoneStorages: array<ZoneStorage>, familyLog: FamilyLog, packaging: array{array{\Admin\Entities\Unit\Unit, float}, array{\Admin\Entities\Unit\Unit, float}|null, array{\Admin\Entities\Unit\Unit, float}|null}, unitPrice: int, minStock: float, quantity: float} $attributes
             */
            static function (array $attributes): Article {
                \assert(\is_string($attributes['name']));
                \assert(\is_string($attributes['uuid']));
                \assert($attributes['supplier'] instanceof Supplier);
                \assert($attributes['tax'] instanceof Tax);
                \assert(\is_array($attributes['zoneStorages']));
                \assert($attributes['familyLog'] instanceof FamilyLog);
                \assert(\is_int($attributes['unitPrice']));
                \assert(\is_float($attributes['minStock']));
                \assert(\is_float($attributes['quantity']));

                $familyLogDomain = $attributes['familyLog']->toDomain($attributes['familyLog']->parent());
                $zoneStoragesDomain = array_map(
                    static fn (ZoneStorage $zoneStorage): \Admin\Entities\ZoneStorage\ZoneStorage => $zoneStorage->toDomain(),
                    $attributes['zoneStorages']
                );

                /** @var array{array{\Admin\Entities\Unit\Unit, float}, array{\Admin\Entities\Unit\Unit, float}|null, array{\Admin\Entities\Unit\Unit, float}|null} $packaging */
                $packaging = $attributes['packaging'];

                $articleDomain = (new ArticleDataBuilder())
                    ->create(
                        $attributes['name'],
                        $attributes['supplier']->toDomain(),
                        $attributes['tax']->toDomain(),
                        $zoneStoragesDomain,
                        $familyLogDomain,
                        $packaging
                    )
                    ->withUuid($attributes['uuid'])
                    ->withAmount($attributes['unitPrice'])
                    ->withMinStock($attributes['minStock'])
                    ->withQuantity($attributes['quantity'])
                    ->build()
                ;

                $zoneStoragesCollection = new ArrayCollection($attributes['zoneStorages']);

                return Article::fromDomain(
                    $articleDomain,
                    $attributes['supplier'],
                    $attributes['tax'],
                    $zoneStoragesCollection,
                    $attributes['familyLog']
                );
            }
        );
    }

    /**
     * @return array{array{\Admin\Entities\Unit\Unit, float}, array{\Admin\Entities\Unit\Unit, float}|null, array{\Admin\Entities\Unit\Unit, float}|null}
     */
    private function generateDefaultPackaging(): array
    {
        $unit = UnitFactory::createOne();

        return [
            [$unit->_real()->toDomain(), self::faker()->randomFloat(3, 1, 10)],
            null,
            null,
        ];
    }
}
