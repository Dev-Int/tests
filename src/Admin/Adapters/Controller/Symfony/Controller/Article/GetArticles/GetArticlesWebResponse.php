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

namespace Admin\Adapters\Controller\Symfony\Controller\Article\GetArticles;

use Admin\Adapters\Controller\Symfony\Controller\Supplier\GetSuppliers\SupplierDto;
use Admin\UseCases\Article\GetArticles\GetArticlesResponse;

final class GetArticlesWebResponse
{
    /** @var array<ArticleDto> */
    private array $articles = [];

    public function __construct(GetArticlesResponse $articles)
    {
        foreach ($articles->articles as $article) {
            $this->articles[] = new ArticleDto(
                $article->uuid()->toString(),
                $article->name()->toString(),
                new SupplierDto(
                    $article->supplier()->uuid()->toString(),
                    $article->supplier()->name()->toString(),
                    $article->supplier()->slug()
                ),
                $article->slug()
            );
        }
    }

    /**
     * @return array<ArticleDto>
     */
    public function articles(): array
    {
        return $this->articles;
    }
}
