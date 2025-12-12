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

use Admin\Entities\Repository\ArticleRepository;

final readonly class GetArticles
{
    public function __construct(private ArticleRepository $articleRepository)
    {
    }

    public function execute(GetArticlesRequest $request): GetArticlesResponse
    {
        $articles = $this->articleRepository->getAllArticlesPaginated($request->page(), $request->itemsPerPage());

        return new GetArticlesResponse($articles);
    }
}
