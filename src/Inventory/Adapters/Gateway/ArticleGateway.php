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
use Inventory\Entities\VO\Article;
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

        // Itérer sur chaque zone pour retourner 1 ligne par article×zone
        foreach ($zoneStorageUuids as $zoneUuid) {
            $articles = $this->articleProvider
                ->forArticles([])
                ->withFilter(ArticleFilter::ZONE_STORAGE, [$zoneUuid])
                ->provideAll()
            ;

            foreach ($articles as $articleResult) {
                yield new Article(
                    uuid: $articleResult->uuid,
                    zoneStorageUuid: $zoneUuid,
                    name: $articleResult->name,
                    unitPrice: $articleResult->unitPrice,
                    quantity: $articleResult->quantity,
                    slug: $articleResult->slug,
                );
            }
        }
    }
}
