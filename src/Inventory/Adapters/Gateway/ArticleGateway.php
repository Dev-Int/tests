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

namespace Inventory\Adapters\Gateway;

use Admin\Contracts\Services\Provider\Article\ArticleFilter;
use Admin\Contracts\Services\Provider\Article\ArticleProvider;
use Admin\Contracts\Services\Provider\Article\Result\PackagingLevelResult;
use Admin\Contracts\Services\Provider\Article\Result\PackagingResult;
use Inventory\Entities\VO\Article;
use Inventory\Entities\VO\PackagingLevel;
use Inventory\Entities\VO\PackagingSnapshot;
use Inventory\UseCases\Gateway\ArticleGatewayInterface;
use Shared\Entities\ResourceUuid;

final readonly class ArticleGateway implements ArticleGatewayInterface
{
    public function __construct(private ArticleProvider $articleProvider)
    {
    }

    /**
     * Retourne une ligne par article×zone.
     *
     * Si un article appartient à plusieurs zones, il sera retourné plusieurs fois,
     * une fois par zone, avec un zoneStorageUuid différent à chaque fois.
     *
     * @param array<ResourceUuid> $zoneStorageUuids
     *
     * @return iterable<Article>
     */
    public function provideForZones(array $zoneStorageUuids): iterable
    {
        if ($zoneStorageUuids === []) {
            return;
        }

        // Single query with all zones - returns one row per article×zone combination
        $articles = $this->articleProvider
            ->forArticles([])
            ->withFilter(ArticleFilter::ZONE_STORAGE, $zoneStorageUuids)
            ->provideAll()
        ;

        foreach ($articles as $articleResult) {
            \assert(
                $articleResult->zoneStorageUuid instanceof ResourceUuid,
                'zoneStorageUuid must be set when filtering by zones'
            );
            \assert(
                $articleResult->packaging instanceof PackagingResult,
                'packaging must be set for inventory creation'
            );

            yield new Article(
                uuid: $articleResult->uuid,
                zoneStorageUuid: $articleResult->zoneStorageUuid,
                name: $articleResult->name,
                unitPrice: $articleResult->unitPrice,
                quantity: $articleResult->quantity,
                slug: $articleResult->slug,
                packaging: $this->mapPackaging($articleResult->packaging),
            );
        }
    }

    private function mapPackaging(PackagingResult $packagingResult): PackagingSnapshot
    {
        return new PackagingSnapshot(
            consumerUnit: $this->mapPackagingLevel($packagingResult->consumerUnit),
            subPackage: $packagingResult->subPackage instanceof PackagingLevelResult
                ? $this->mapPackagingLevel($packagingResult->subPackage)
                : null,
            parcel: $packagingResult->parcel instanceof PackagingLevelResult
                ? $this->mapPackagingLevel($packagingResult->parcel)
                : null,
        );
    }

    private function mapPackagingLevel(PackagingLevelResult $levelResult): PackagingLevel
    {
        return new PackagingLevel(
            unitLabel: $levelResult->unitLabel,
            unitAbbreviation: $levelResult->unitAbbreviation,
            quantity: $levelResult->quantity,
        );
    }
}
