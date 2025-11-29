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
use Admin\Adapters\Gateway\ORM\Entity\FamilyLog\FamilyLog;
use Admin\UseCases\Article\GetArticles\GetArticlesResponse;

final class GetArticlesWebResponse
{
    /** @var array<ArticleDto> */
    private array $articles = [];
    private int $totalItems;

    public function __construct(GetArticlesResponse $response)
    {
        foreach ($response->articles->toArray() as $article) {
            $this->articles[] = new ArticleDto(
                $article->uuid()->toString(),
                $article->name()->toString(),
                new SupplierDto(
                    $article->supplier()->uuid()->toString(),
                    $article->supplier()->name()->toString(),
                    (new FamilyLog())->fromDomain($article->supplier()->familyLog()),
                    $article->supplier()->slug()
                ),
                $article->slug()
            );
        }

        $this->totalItems = $response->articles->count();
    }

    /**
     * @return array<ArticleDto>
     */
    public function articles(): array
    {
        return $this->articles;
    }

    public function totalItems(): int
    {
        return $this->totalItems;
    }
}
