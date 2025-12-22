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

namespace Admin\Contracts\Services\Provider\Article;

use Admin\Contracts\Services\Provider\Article\Result\ArticleResult;
use Admin\Contracts\Services\Provider\Exception\ArticleNotFound;
use Shared\Entities\ResourceUuid;

interface ArticleProvider
{
    /**
     * @throws ArticleNotFound
     */
    public function provide(ResourceUuid $uuid): ArticleResult;

    /**
     * @param array<int, ResourceUuid> $articleIds
     */
    public function forArticles(array $articleIds): ArticleAggregatorBuilder;
}
