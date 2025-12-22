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

namespace Admin\Adapters\Gateway\Contracts\Provider\Article;

use Admin\Contracts\Services\Provider\Article\ArticleAggregatorBuilder;
use Admin\Contracts\Services\Provider\Article\ArticleProvider as ArticleProviderContract;
use Admin\Contracts\Services\Provider\Article\Result\ArticleResult;
use Admin\Contracts\Services\Provider\Exception\ArticleNotFound;
use Admin\Entities\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Shared\Entities\ResourceUuid;

final readonly class ArticleProvider implements ArticleProviderContract
{
    public function __construct(
        private ArticleRepository $repository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function provide(ResourceUuid $uuid): ArticleResult
    {
        try {
            $article = $this->repository->getByUuid($uuid);
        } catch (\Throwable) {
            throw new ArticleNotFound($uuid->toString());
        }

        return new ArticleResult(
            uuid: $uuid,
            name: $article->name(),
            unitPrice: $article->unitPrice(),
            quantity: $article->quantity()->toUnit(),
            slug: $article->slug()
        );
    }

    public function forArticles(array $articleIds): ArticleAggregatorBuilder
    {
        return new DefaultArticleAggregatorBuilder(
            $this->entityManager,
            array_values($articleIds)
        );
    }
}
