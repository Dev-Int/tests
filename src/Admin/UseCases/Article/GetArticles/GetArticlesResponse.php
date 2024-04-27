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

namespace Admin\UseCases\Article\GetArticles;

use Admin\Entities\Article\ArticleCollection;

final readonly class GetArticlesResponse
{
    public function __construct(public ArticleCollection $articles)
    {
    }
}
