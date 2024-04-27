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

use Admin\UseCases\Gateway\ArticleRepository;

final readonly class GetArticles
{
    public function __construct(private ArticleRepository $articleRepository)
    {
    }

    public function execute(): GetArticlesResponse
    {
        $articles = $this->articleRepository->findAllArticles();

        return new GetArticlesResponse($articles);
    }
}
