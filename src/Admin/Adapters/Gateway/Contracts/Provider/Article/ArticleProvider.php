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

use Admin\Contracts\Services\Provider\Article\ArticleProvider as ArticleProviderContract;
use Admin\Contracts\Services\Provider\Article\Result\Article;
use Admin\Contracts\Services\Provider\Exception\ArticleNotFound;
use Admin\Entities\Repository\ArticleRepository;
use Shared\Entities\ResourceUuid;

final readonly class ArticleProvider implements ArticleProviderContract
{
    public function __construct(private ArticleRepository $repository)
    {
    }

    public function provide(ResourceUuid $uuid): Article
    {
        try {
            $article = $this->repository->getByUuid($uuid);
        } catch (\Throwable) {
            throw new ArticleNotFound($uuid->toString());
        }

        return new Article(
            uuid: $uuid,
            name: $article->name(),
            unitPrice: $article->unitPrice(),
            quantity: $article->quantity()->toUnit(),
            slug: $article->slug()
        );
    }

    public function provideAll(iterable $ids): iterable
    {
        foreach ($ids as $id) {
            yield $this->provide($id);
        }
    }
}
