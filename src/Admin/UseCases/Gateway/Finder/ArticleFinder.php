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

namespace Admin\UseCases\Gateway\Finder;

use Admin\Entities\Article\Article;
use Shared\Entities\ResourceUuid;

interface ArticleFinder
{
    public function findByUuid(ResourceUuid|string $uuid): ?Article;

    /**
     * @return iterable<Article>
     */
    public function findAllArticles(): iterable;

    /**
     * @param string[] $uuids
     *
     * @return iterable<Article>
     */
    public function findByUuids(array $uuids): iterable;
}
