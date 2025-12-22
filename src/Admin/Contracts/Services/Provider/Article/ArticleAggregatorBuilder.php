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

use Admin\Contracts\Services\Provider\Article\Result\ArticleCollectionResult;
use Shared\Entities\Enum\QueryOrder;
use Shared\Entities\ResourceUuid;

interface ArticleAggregatorBuilder
{
    /**
     * @param array<ResourceUuid>|bool|ResourceUuid $value
     */
    public function withFilter(ArticleFilter $filter, array|bool|ResourceUuid $value): self;

    public function limit(int $limit): self;

    public function offset(int $offset): self;

    public function orderBy(ArticleOrderField $field, QueryOrder $direction = QueryOrder::ASC): self;

    public function provideAll(): ArticleCollectionResult;
}
